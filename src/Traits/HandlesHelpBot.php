<?php

namespace LaravelSklearnBot\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

//helper
use LaravelSklearnBot\Scanners\HelpBotScanner;

//Models
use LaravelSklearnBot\Models\Helpbot;

trait HandlesHelpBot
{

    private function _onProvider(){
       return config('sklearnbot.helpbot');
    }

    /**
     * @return bool
     */
    private function _isModel()
    {
        $file_model = config('sklearnbot.python.helpbot_pkl');
        $file_output = config('sklearnbot.python.helpbot_output_pkl');

        $modelExists = Storage::exists('sklearnbot/' . $file_model);
        $outputExists = Storage::exists('sklearnbot/' . $file_output);

        if ($modelExists && $outputExists) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $data
     * 
     * @return object
     */
    private function _validateParserHelpBotModel($data)
    {
        $rules = [
            'id' => 'required',
            'hash' => 'required|string', 
            'title' => 'required|string',    
            'text' => 'required|string',          
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator;
    }

    /**
     * Scan all the collectors and fill in the database
     * @return bool
     */
    public function parserHelpBotModel(){
        try {
            $helpBotScanner = new HelpBotScanner();
            $scanResult = $helpBotScanner->scan();
            if(is_array($scanResult)){
                foreach($scanResult as $items){
                    
                    $type = $items['type'];  

                    if(is_array($items['result'])){

                        $newResultData = [];

                        foreach($items['result'] as $result){
                            
                            if(!empty($result['id']) && !empty($type)){
    
                                $result['hash'] = md5($result['id'].$type);
        
                                $this->_validateParserHelpBotModel($result);

                                $isNewItem = false;
        
                                if($newHelpbot = Helpbot::where('hash',$result['hash'])->first()){
                                    if (method_exists($items['class'], 'eventBeforeUpdate')) {
                                        $items['class']->eventBeforeUpdate($newHelpbot);
                                    }
                                    $isNewItem = false;
                                }else{
                                    $newHelpbot = new Helpbot();
                                    $newHelpbot->hash = $result['hash'];
                                    $newHelpbot->type = $type;
                                    if (method_exists($items['class'], 'eventBeforeCreating')) {
                                        $items['class']->eventBeforeCreating($newHelpbot);
                                    }
                                    $isNewItem = true;
                                }
                            
                                $newHelpbot->title = $result['title'];
                                $newHelpbot->text = $result['text'];
                                $newHelpbot->belongs_to = $result['belongs_to'] ?? null;

                                $newHelpbot->save();

                                $newHelpbot->action = $result['action'] ?? [];
                                $newHelpbot->synonym = $result['synonym'] ?? [];
                                $newHelpbot->push();

                                if (method_exists($items['class'], 'eventAfterCreatedOrUpdated')) {
                                    $items['class']->eventAfterCreatedOrUpdated($newHelpbot);
                                }

                                $newResultData[] = [
                                    'hash' => $newHelpbot->hash,
                                    'id' => $newHelpbot->id,
                                    'type' => $newHelpbot->type,
                                    'title' => $newHelpbot->title,
                                    'text' => $newHelpbot->text,
                                    'synonym' => $newHelpbot->synonym ?? [],
                                    'action' => $newHelpbot->action ?? [],
                                    'belongs_to' => $newHelpbot->belongs_to ?? '',
                                ];
        
                            }
                        }

                        if($isNewItem && $this->_onProvider() == 'python'){
                            if(config('sklearnbot.python.helpbot_model_addtraining') && $this->_isModel()){
                                $this->fineTuneModelHelpBot($newResultData);
                            }
                        }
                    }         
                }
            }
            return true;
        } catch (Exception $e) {
            Log::error('Failed to parser HelpBot model: ' . $e->getMessage());
            return false;
        }

    }

    /**
     * Trening Model HelpBot
     * the model will be trained from scratch, removing all previous data
     * @return bool
     */
    public function trainModelFromScratch(){
        try {
            $helpbot = App::make('Helpbot');
            $allHelpbots = $allHelpbots = $helpbot->select('id','title', 'text', 'belongs_to', 'action', 'synonym','type','hash')->get();
            if($allHelpbots){
                Storage::put('sklearnbot/helpbot.json', json_encode($allHelpbots));
                if($this->createModelHelpBot()){
                    return true;
                }  
            }
        } catch (Exception $e) {
            Log::error('Failed to train HelpBot model: ' . $e->getMessage());
        }

        return false;
    }


    /**
     * Create Model HelpBot
     * @return bool
     */
    public function createModelHelpBot()
    {
        try {
            $response = $this->sendPythonRequest('/create-model');
            return $response['status'] === 'success';
        } catch (Exception $e) {
            Log::error('Failed to create HelpBot model: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reload Model HelpBot
     * @return bool
     */
    public function reloadModelHelpBot()
    {
        try {
            $response = $this->sendPythonRequest('/reload-model');
            return $response['status'] === 'success';
        } catch (Exception $e) {
            Log::error('Failed to reload HelpBot model: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fine-tune Model HelpBot
     * @param array $data
     * @return bool
     */
    public function fineTuneModelHelpBot(array $data)
    {
        try {
            $response = $this->sendPythonRequest('/fine-tune-model', $data);
            return $response['status'] === 'success';
        } catch (Exception $e) {
            Log::error('Failed to fine-tune HelpBot model: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Response from HelpBot
     * @param string $text
     * @param string|null $belongsTo
     * @return array
     */
    public function getHelpBotResponse(string $text, string $belongsTo = null)
    {
        try {
            $data = ['text' => $text];
            if ($belongsTo) {
                $data['belongs_to'] = $belongsTo;
            }
            return $this->sendPythonRequest('/get-response', $data);
        } catch (Exception $e) {
            Log::error('Failed to get HelpBot response: ' . $e->getMessage());
            return ['error' => 'Failed to get response'];
        }
    }

    /**
     * Send request to Python Flask app
     * 
     * @param string $endpoint The endpoint to send the request to.
     * @param array|null $data The data to send in the request body.
     * @return array The response from the Flask app as an array.
     * @throws Exception If there is an error in the request.
     */
    private function sendPythonRequest(string $endpoint, array $data = null, $timeout = 30)
    {
        $baseUrl = config('sklearnbot.python.helpbot_host') . ':' . config('sklearnbot.python.helpbot_port');
        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        $headers = [
            'Authorization' => config('sklearnbot.python.helpbot_token'),
            'Content-Type' => 'application/json',
        ];

        try {
            $response = \Http::withHeaders($headers)->timeout($timeout);

            if ($data) {
                $response = $response->post($url, $data);
            } else {
                $response = $response->post($url);
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error("Error in sendPythonRequest (URL: $url): " . $e->getMessage());
            throw $e;
        }
    }
}