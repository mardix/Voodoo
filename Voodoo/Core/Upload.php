<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 * 
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 * 
 * @name        Core\Upload
 * @since       --
 * @desc        Class to upload file to the server
 * 
 */
/*
To upload file to the server

Upload::File('filename')
        ->setName('mypicture')
        ->setExtensions("jpg,png,gif")
        ->setMaxSize(5,"M")
        ->setOverwrite(true)
        ->uploadTo(/my_destination/dir)


When file is uploaded with no error, you can access the following properties
 * $this->file = the full file path
 * $this->fileName = the file name
 * $this->fileExt =  the file extension

*/


//------------------------------------------------------------------------------

namespace Voodoo\Core;

Class Upload{

        public static function File($fieldName){
            return
                new self($fieldName);
        }
        
        /**
         * 
         * @param type $fieldName
         * @param type $toDir
         * @return Upload 
         */
        public function __construct($fieldName){
         $this->fieldName = $fieldName;
        }
        
        /**
         * Rename after upload. Just enter a name with no extensions
         */
        public function setName($newName){
             $this->rename = $newName;
             return $this;
        }
        
        public function saveTo($dir){
            $this->toDir = $dir;
            
            return
                $this;
        }
        
        /**
         * set the extensions that are allowed, separated by comma
         */
        public function setExtensions($ext){
          $this->allowedExt = explode(",",strtolower($ext));
          return $this;
        }
        
        /**
         * The maximum size of the file
         * @param <type> $maxSize
         * @return <type>
         */
        public function setMaxSize($maxSize,$size="M"){
             $sizeR = ($size=="M") ? 1000 : 1;
             $this->maxSize = $maxSize * (1024 * $sizeR);
             return $this;
        }
        
        /**
         * Allow overwrite
         * @return <type>
         */
        public function setOverwrite($bool=true){
             $this->overwrite = $bool;
             return $this;
        }
        
        
        /**
         * To copy the uploaded file to a new place
         * @param type $newDir
         * @param type $move
         * @return string, the path of the new file
         * @throws Exception 
         */
        public function copyTo($newDir,$move = false){
            
            $src = $this->file;
            $dest = $newDir."/".$this->fileName;
            
            if(!is_dir($newDir))
                throw new Exception("Not a valid directory: {$newDir}");
            
                
            if(file_exists($src)){
                if(!($move) ? rename($src,$dest) : copy($src,$dest))
                   throw new Exception("Failed to COPY {$src} to {$dest}");
                
                else
                    $dest;
            }
            
            else
                throw new Exception("Source file: {$src} doesn't exist");

        }


        /**
         * Reset the ini
         * @param <type> $Memory
         * @param <type> $PostMax
         * @param <type> $UploadMax
         * @return <type>
         */
        public function resetIni($Memory,$PostMax,$UploadMax){
            ini_set("memory_limit",$Memory);
            ini_set("post_max_size",$PostMax);
            ini_set("upload_max_filesize",$UploadMax);
           return $this;
        }



        public function uploadTo($dir){
            
            $this->saveTo($dir);
            
            if(!$this->exec())
                throw new Exception($this->error_msg,$this->error_code);
            
            return
                $this;
        }



        /**
         * Execute
         * @return <type>
         */
        public function exec(){

            if(is_uploaded_file($_FILES[$this->fieldName]['tmp_name'])){

               $file = $_FILES[$this->fieldName]['name'];

               $fileInfo = pathinfo($file);

               $this->fileExt = strtolower($fileInfo[extension]);

               // Clean name
               $newFileName = strtolower(preg_replace("/[^a-z0-9\._]/i","_",$fileInfo[filename]));

               $this->fileName = (($this->rename) ? $this->rename : $newFileName).".{$this->fileExt}";

               $this->file = $this->toDir."/".$this->fileName;


               // FIle existence
               if(file_exists($this->file)){
                 if($this->overwrite)
                    unlink($this->file);

                  else{
                    $this->error = true;
                    $this->error_code = 2;
                    $this->error_msg ="File exists, can't overwrite";
                    return false;
                  }
               }


               // Check extensions
                if($this->allowedExt && !in_array(strtolower($this->fileExt),$this->allowedExt)){
                    $this->error = true;
                    $this->error_code = 4;
                    $this->error_msg = "File is not allowed";
                    return false;
                }


                // File size
                if(filesize($_FILES[$this->fieldName]['tmp_name']) > $this->maxSize){
                   $this->error = true;
                   $this->error_code = 3;
                   $this->error_msg = "File is too big";
                   return false;
                }


                if(!$this->error){

                    if (move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $this->file)){
                      return true;
                    }

                    else{
                      $this->error = true;
                      $this->error_code = 11;
                      $this->error_msg = "File couldn't be saved";
                      return false;
                    }
                }
            }

            else{
              $this->error = true;
              $this->error_code=10;
              $this->error_msg = "File couldn't be uploaded";
              return false;

            }
        }

}
