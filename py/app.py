import json
import pickle
import os
import logging
from flask import Flask, request, jsonify
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from functools import wraps

app = Flask(__name__)

# Setting up logging
logging.basicConfig(level=logging.INFO)

# Paths to model and data files
MODEL_PATH = './storage/app/sklearnbot/model.pkl'
OUTPUT_DATA_PATH = './storage/app/sklearnbot/output.pkl'
DATA_FILE_PATH = './storage/app/sklearnbot/helpbot.json'

# Global variables to store model and data
model = None
output_data = None
texts = None
question_vectors = None

# Token for access control
ACCESS_TOKEN = "TESTS"  # Set your token here

def check_token(token):
    """Check if the provided token matches the access token."""
    return token == ACCESS_TOKEN

def token_required(f):
    """Decorator to check token in request headers."""
    @wraps(f)
    def decorator(*args, **kwargs):
        token = request.headers.get('Authorization')
        if not token or not check_token(token):
            return jsonify({"status": "error", "message": "Unauthorized"}), 401
        return f(*args, **kwargs)
    return decorator



def load_model_and_data():
    global model, output_data, texts, question_vectors
    
    try:
        # Ensure the model and output data files exist
        if not os.path.exists(DATA_FILE_PATH):
            raise FileNotFoundError(f"Data file '{DATA_FILE_PATH}' not found. Cannot load model.")

        # Loading the trained model and data
        with open(MODEL_PATH, 'rb') as f:
            model = pickle.load(f)

        with open(OUTPUT_DATA_PATH, 'rb') as f:
            output_data = pickle.load(f)

        # Preparing data for search
        texts = list(output_data.keys())
        question_vectors = model.transform(texts)  # Vectorizing all texts

        logging.info("Model and data loaded successfully.")
    except Exception as e:
        logging.error(f"Error loading model and data: {str(e)}")
        raise


def create_model_and_data(data_file=DATA_FILE_PATH):
    try:
        if not os.path.exists(data_file):
            raise FileNotFoundError(f"File '{data_file}' not found. Cannot create model without data.")

        # Loading data from JSON
        with open(data_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
        logging.info(f"Data successfully loaded from '{data_file}'")

        # Checking for required fields
        required_fields = {'title', 'text', 'id'}
        
        # Preparing data
        texts = []
        output_data = {}

        for entry in data:
            if not required_fields.issubset(entry.keys()):
                raise ValueError(f"Insufficient data in entry: {entry}")

            title = entry['title'].lower()
            dtext = entry['text'].lower()
            synonyms = " ".join(entry.get('synonym', [])).lower()
            full_text = f"{title} {synonyms} {dtext}"  # Composing the full text
            texts.append(full_text)

            # Creating an entry with condition for the 'belongs_to' field
            entry_data = {
                "id": entry['id'],
                "type": entry.get('type', 'unknown'), 
                "title": entry['title'],
                "text": entry['text'],
                "synonym": entry.get('synonym', []),
                "action": entry.get('action', [])
            }

            # Adding 'belongs_to' field only if present
            if 'belongs_to' in entry:
                entry_data["belongs_to"] = entry['belongs_to']
            
            output_data[full_text] = entry_data

        # Converting texts to vector representation
        vectorizer = TfidfVectorizer(max_df=0.95, min_df=1, ngram_range=(1, 3))
        X = vectorizer.fit_transform(texts)
        logging.info("Texts successfully vectorized")

        # Saving the vectorizer and data
        with open(MODEL_PATH, 'wb') as f:
            pickle.dump(vectorizer, f)
        logging.info(f"Vectorizer successfully saved to '{MODEL_PATH}'")

        with open(OUTPUT_DATA_PATH, 'wb') as f:
            pickle.dump(output_data, f)
        logging.info(f"Data successfully saved to '{OUTPUT_DATA_PATH}'")

        # Reloading model and data
        load_model_and_data()

        return {"status": "success", "message": "Model and data successfully created and loaded."}
    
    except FileNotFoundError as e:
        logging.error(str(e))
        return {"status": "error", "message": str(e)}
    except ValueError as ve:
        logging.error(f"Data error: {str(ve)}")
        return {"status": "error", "message": f"Data error: {str(ve)}"}
    except Exception as e:
        logging.error(f"An error occurred: {str(e)}")
        return {"status": "error", "message": f"An error occurred: {str(e)}"}

# Function to get a response to a question
def get_response(user_question, belongs_to=None):
    user_question = user_question.lower()

    try:
        # Vectorizing the question
        user_vector = model.transform([user_question])

        # Calculating cosine similarity
        similarities = cosine_similarity(user_vector, question_vectors).flatten()

        # Sorting by similarity
        sorted_indices = similarities.argsort()[::-1]
        best_match = None
        best_similarity = 0

        # Searching for the most appropriate answer
        for idx in sorted_indices:
            matched_text = texts[idx]
            matched_data = output_data.get(matched_text, {})

            # Checking for 'belongs_to' if specified
            if belongs_to:
                if matched_data.get('belongs_to') == belongs_to:
                    best_match = matched_data
                    best_similarity = similarities[idx]
                    break
            else:
                # If 'belongs_to' is not specified, select only if similarity is significant
                if similarities[idx] > best_similarity and not matched_data.get('belongs_to'):
                    best_match = matched_data
                    best_similarity = similarities[idx]

        # Returning the response if a suitable match is found
        if best_match and best_similarity > 0.1:
            response = {
                "id": best_match.get('id', "unknown"),
                "type": best_match.get('type', "unknown"),
                "text": best_match.get('text', "unknown"),
            }

            if 'action' in best_match:
                response["action"] = best_match['action']

            return response

        # Returning an error if no suitable match is found
        return {"error": "Could not find a suitable response."}

    except Exception as e:
        logging.error(f"Error processing request: {str(e)}")
        return {"error": f"Error processing request: {str(e)}"}

def fine_tune_model(new_data):
    global model, output_data, texts, question_vectors
    
    try:
        # Adding or updating data
        for entry in new_data:
            entry_id = entry.get('id')
            if not entry_id:
                continue

            title = entry.get('title', '').lower()
            text = entry.get('text', '').lower()
            full_text = f"{title} {text}"  # Composing the full text

            entry_data = {
                "id": entry_id,
                "type": entry.get('type', 'unknown'), 
                "title": entry.get('title', ''),
                "text": entry.get('text', ''),
                "synonym": entry.get('synonym', []),
                "action": entry.get('action', [])
            }

            if 'belongs_to' in entry:
                entry_data["belongs_to"] = entry['belongs_to']

            # Update existing entry or add new one
            existing_texts = [text for text in texts if output_data[text]['id'] == entry_id]
            if existing_texts:
                for existing_text in existing_texts:
                    texts.remove(existing_text)
                    del output_data[existing_text]

            # Add new text and data
            texts.append(full_text)
            output_data[full_text] = entry_data

        # Revectorizing texts
        vectorizer = TfidfVectorizer(max_df=0.95, min_df=1, ngram_range=(1, 3))
        X = vectorizer.fit_transform(texts)
        model = vectorizer

        logging.info("Model successfully updated with new data")

        # Save updated model and data
        with open(MODEL_PATH, 'wb') as f:
            pickle.dump(model, f)
        logging.info(f"Updated vectorizer saved to '{MODEL_PATH}'")

        with open(OUTPUT_DATA_PATH, 'wb') as f:
            pickle.dump(output_data, f)
        logging.info(f"Updated data saved to '{OUTPUT_DATA_PATH}'")

        # Reload the updated model and data
        load_model_and_data()

        return {"status": "success", "message": "Model successfully fine-tuned with new data."}

    except Exception as e:
        logging.error(f"Error fine-tuning model: {str(e)}")
        return {"status": "error", "message": f"Error fine-tuning model: {str(e)}"}


#ex
if not os.path.exists(OUTPUT_DATA_PATH):
    create_model_and_data()
else:
    load_model_and_data()

# Route to fine-tune the model
@app.route('/fine-tune-model', methods=['POST'])
@token_required
def fine_tune_model_route():
    data = request.json
    if not isinstance(data, list):
        return jsonify({"status": "error", "message": "Data should be a list of entries."}), 400
    
    result = fine_tune_model(data)
    return jsonify(result)

# Route to create model and data
@app.route('/create-model', methods=['POST'])
@token_required
def create_model_route():
    try:
        result = create_model_and_data()
        return jsonify(result)
    except Exception as e:
        logging.error(f"Error creating model: {str(e)}")
        return jsonify({"status": "error", "message": f"Error creating model: {str(e)}"}), 500

# Route to handle POST requests
@app.route('/get-response', methods=['POST'])
def get_response_route():
    data = request.json
    text = data.get('text')
    belongs_to = data.get('belongs_to')
    
    if not text:
        return jsonify({"error": "The 'text' field is required."}), 400
    
    response = get_response(text, belongs_to)
    return jsonify(response)

# Route to reload model and data
@app.route('/reload-model', methods=['POST'])
@token_required
def reload_model():
    try:
        load_model_and_data()
        return jsonify({"status": "success", "message": "Model and data successfully reloaded."}), 200
    except Exception as e:
        logging.error(f"Error reloading: {str(e)}")
        return jsonify({"status": "error", "message": f"Error reloading: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5729)

# Example testing with curl:
# curl -X POST http://127.0.0.1:5729/create-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
# curl -X POST http://127.0.0.1:5729/reload-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
# curl -X POST http://127.0.0.1:5729/fine-tune-model -H "Content-Type: application/json" -H "Authorization: your-secure-token" -d '[{"id":"3","title":"boba","text":"aboba"}]'
# curl -X POST http://127.0.0.1:5729/get-response -H "Content-Type: application/json" -d '{"text":"boba"}'
