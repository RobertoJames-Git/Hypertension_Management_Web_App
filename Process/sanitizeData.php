<?php

    function sanitizeUserInput($allUserInput){
            //Calls a user defined function to sanitize input 
            $sanitized_post = [];
            foreach ($allUserInput as $key => $value) {
                // Remove any leading or trailing whitespace
                $trimmedValue = trim($value);
                // Convert special characters into HTML entities to prevent XSS attacks
                $sanitized_post[$key] = filter_var($trimmedValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
            }
            return $sanitized_post; 
                
    }
