# Installation Guide for Flask-based Text Similarity Service

This guide will help you set up and run a Flask-based text similarity service on your local machine. The service allows you to create and load a text similarity model using TF-IDF vectorization and respond to queries by identifying the most similar text from a pre-defined dataset.

## Prerequisites

1. **Python 3.6+**: Make sure Python is installed on your machine.
   - You can download it from the [official Python website](https://www.python.org/downloads/).

2. **pip**: Python's package installer should be installed with Python. Verify it by running:
   <code>
   pip --version
   </code>

3. **Git** (optional): For version control and easy project setup.

## Step 1: Set Up a Virtual Environment (Recommended)

A virtual environment isolates the dependencies of this project from your global Python installation, which helps to avoid version conflicts.

1. **Navigate to your project directory**:
   <code>
   cd /path/to/your/project
   </code>

2. **Create a virtual environment**:
   <code>
   python -m venv venv
   </code>

3. **Activate the virtual environment**:
   - On **Windows**:
     <code>
     venv\Scripts\activate
     </code>
   - On **macOS/Linux**:
     <code>
     source venv/bin/activate
     </code>

## Step 2: Install Required Python Packages

With the virtual environment activated, install the necessary dependencies using `pip`:

```
pip install flask scikit-learn
```

## Step 3: Prepare Your Dataset

You need to have a `data.json` file that contains the dataset. Each entry in the JSON file should have the following fields:

- `id`: Unique identifier for the entry.
- `title`: The title of the entry.
- `description`: A description or content of the entry.
- `synonym` (optional): A list of synonyms related to the entry.
- `belongs_to` (optional): Categorization or group the entry belongs to.
- `type` (optional): The type of the entry.
- `action` (optional): Any associated actions.

Here’s an example of the `data.json` file format:

```json
[
    {
        "id": "1",
        "title": "Example Title",
        "description": "This is an example description.",
        "synonym": ["example", "sample"],
        "belongs_to": "category1",
        "type": "type1",
        "action": ["action1"]
    }
]
```

## Step 4: Save the Python Script

Save the provided Python script to a file named `app.py` in your project directory.

## Step 5: Run the Flask Application

Run the Flask application with the following command:

`
python app.py
`

By default, the application will start on `http://127.0.0.1:5729`.

## Step 6: Use the API Endpoints

You can interact with the API using tools like `curl` or Postman. Here are some example `curl` commands:

1. **Creating a model**:

```
curl -X POST http://127.0.0.1:5729/create-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
```

2. **Rebooting the model**:

```
curl -X POST http://127.0.0.1:5729/reload-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
```

3. **Fine-tuning the model**:

```
curl -X POST http://127.0.0.1:5729/fine-tune-model -H "Content-Type: application/json" -H "Authorization: your-secure-token" -d '[{"id":"3","title":"boba","text":"aboba"}]'
```

4. **Getting a response to a request**:

```
curl -X POST http://127.0.0.1:5729/get-response -H "Content-Type: application/json" -d '{"text":"boba"}'
```

## Troubleshooting

- **FileNotFoundError**: Ensure that the `data.json` file is present in the same directory as `app.py`.
- **ValueError**: Verify that each entry in your JSON file contains the required fields.
- **Other Errors**: Check the logs for detailed error messages.

For more information, refer to the [Flask documentation](https://flask.palletsprojects.com/) and [scikit-learn documentation](https://scikit-learn.org/stable/).