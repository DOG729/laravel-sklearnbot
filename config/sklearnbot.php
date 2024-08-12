<?php


return [
    
    /**
     * logic for the operation of submodules
     */
    'helpbot' => 'python', //python or php
    'search' => 'php', //php
    
    /**
     * What logic should I use for learning
     */
    'python' => [

        'run' => 'app.py',

         /**
         * host:port for the running flash application
         */
        'helpbot_token' => ENV('HELPBOT_TOKEN'),
        'helpbot_host' => ENV('HELPBOT_FLASK_HOST',"127.0.0.1"),
        'helpbot_port' => ENV('HELPBOT_FLASK_PORT',"5729"),

        /**
         * The name of the model files
         */
        'helpbot_model' => 'helpbot',
        'helpbot_model_addtraining' => true, //auto further training of the model
        'helpbot_pkl' => ENV('HELPBOT_PKL','model.pkl'), //model helpbot
        'helpbot_output_pkl' => ENV('HELPBOT_OUTPUT_PKL','output.pkl'), //model helpbot database

        'search_model' => 'search',
        'search_pkl' => ENV('SEARCH_PKL','search.pkl'), //model search
        'search_output_pkl' => ENV('SEARCH_OUTPUT_PKL','search_output.pkl'), //model search database
    ],
    

];