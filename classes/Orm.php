<?php
/*
 * Author: Rafael Rocha - www.rafaelrocha.net - info@rafaelrocha.net
 * 
 * http://projects.rafaelrocha.net/ -> libraries
 *
 * Date: 27.02.2009
 * 
 * Version: 1.1
 * 
 * Bugs fixed in 1.1: 
 * 1� Mysqli query function
 * 2� Save as new function on classes
 * Bugs still hapen:
 * 1� table keys in end of table missing. So don�t let your table keys in end of table fields
 * 
 * License: LGPL 
 * 
 * What do: This is a class to convert your mysql
 * tables into php classes. YOU NEED TO HAVE MYSQLI CLASS (php v5.0) IN YOUR HOST
 * 
 */
require_once("config.php");

class Orm
{

    //DataBase settings
    private $table;
    private $name;
    private $classname;
    private $namespace;
    private $path;
    private $time;

    //functions Settings
    private $construct = true;
    private $geters = true;
    private $seters = true;
    private $load_row_from_key = true; // get_record
    private $delete_row_from_key = true; // delete
    private $save_active_row = true; // update
    private $save_active_row_as_new = true; // insert
    private $Order_Keys = true;

    //tables name and variables names and setings of variables
    private $classe_name = array(array('name', 'total_vars'));
    private $classe_variables = array(array('var_name', 'length', 'key'));

    //statistics
    private $num_of_tables = 1;
    private $num_of_total_variables = 0;


    /**
     * Gets tablename and class name
     *
     * @param string $table
     * @param string $namespace
     * @param string $classname
     */
    public function __construct($table, $namespace, $classname)
    {
        global $CFG;
        $this->table = $table;
        $this->classname = $classname;
        $this->namespace = $namespace;
        $this->path = $CFG->tempdir . '/orm';
        $this->time = time();
    }


    /**
     * This is the unic function public. You just need to run this function
     * to have you classes files
     * This gonne read all tables and columns and save it into class vars array
     * After read one complete table, create the file
     *
     */
    public function CreateClassesFiles()
    {
        global $DB;

        $this->classe_name[$this->num_of_tables]['name'] = $this->classname; //get the name of the table/class
        $this->classe_name[$this->num_of_tables]['total_vars'] = 0;
        $columns = $DB->get_columns($this->table);
        $j = 0;
        foreach ($columns as $col) {
            $this->classe_variables[$j]['var_name'] = $col->name;
            $this->classe_variables[$j]['length'] = $col->type . " (" . $col->max_length . ")";
            $this->classe_variables[$j]['type'] = $col->type;
            $this->classe_variables[$j]['key'] = $col->primary_key;
            $this->num_of_total_variables += 1;
            $this->classe_name[$this->num_of_tables]['total_vars'] += 1;
            $j++;
        }
        $this->CreatFiles();

        $filename = $this->ZipPackage();
        return $filename;
    }


    /**
     * By using the class vars, will be now create what file will be have inside, and what functions
     * will be able to use
     *
     */
    private function CreatFiles()
    {
        global $CFG;

        $this->createCrudFile();

        $file_in = "<?php\n";
        $file_in .= $this->Copyright();
        $file_in .= "\nnamespace " . $this->namespace . ";\n";
        $file_in .= "\nuse " . $this->namespace . "\crud;\n";
        $file_in .= "\nclass " . $this->classname . " extends crud {\n\n";

        $file_in = $this->CreateVars($file_in);

        if ($this->construct) $file_in = $this->CreateConstruct($file_in);

        if ($this->seters) $file_in = $this->CreateFunctionGetters($file_in);

        if ($this->geters) $file_in = $this->CreateFunctionSetters($file_in);

        $file_in .= "\n}";

        $this->SaveFile($this->classname . ".php", $file_in);
        // Create search all records class
        $this->CreateSearchRecordsFile();
    }

    private function createCrudFile() {
        $file_in = "<?php\n";
        $file_in .= $this->Copyright();
        $file_in .= "\nnamespace " . $this->namespace . ";\n";
        $file_in .= "\nabstract class crud {\n\n";

        $file_in .=  "\n/**";
        $file_in .=  "\n/* string";
        $file_in .=  "\n**/";
        $file_in .=  "\nprivate \$table;\n";

        $file_in .=  "\n/**";
        $file_in .=  "\n/* int";
        $file_in .=  "\n**/";
        $file_in .=  "\nprivate \$id;\n";

        if ($this->load_row_from_key) $file_in = $this->CreateFunctionGetRecord($file_in);

        if ($this->delete_row_from_key) $file_in = $this->CreateFunctionDeleteRecord($file_in);

        if ($this->save_active_row) $file_in = $this->CreateFunctionInsertRecord($file_in);

        if ($this->save_active_row_as_new) $file_in = $this->CreateFunctionUpdateRecord($file_in);

        $file_in .=  "\n/**";
        $file_in .=  "\n/* get id";
        $file_in .=  "\n**/";
        $file_in .=  "\npublic function get_id() {\n";
        $file_in .=  "  return \$this->id;\n";
        $file_in .=  "}\n";

        $file_in .=  "\n/**";
        $file_in .=  "\n/* get table";
        $file_in .=  "\n**/";
        $file_in .=  "\npublic function get_table() {\n";
        $file_in .=  "  return \$this->table;\n";
        $file_in .=  "}\n";

        $file_in .= "\n}";

        $this->SaveFile( "crud.php", $file_in);
    }

    private function CreateSearchRecordsFile()
    {
        // Create file structure
        $file_in = "<?php\n";
        $file_in .= $this->Copyright();
        $file_in .= "\nnamespace " . $this->namespace . ";\n";
        $file_in .= "\nclass " . $this->classname . "s {\n";

        $file_in .= "\n	/**\n";
        $file_in .= "	 *\n";
        $file_in .= "	 *@var string\n";
        $file_in .= "	 */\n";
        $file_in .= "	private \$results;\n";
        $file_in .= "\n	/**\n";
        $file_in .= "	 *\n";
        $file_in .= "	 *@global \moodle_database \$DB\n";
        $file_in .= "	 */\n";
        $file_in .= "	public function __construct() {\n";
        $file_in .= "	    global \$DB;\n";
        $file_in .= "	    \$this->results = \$DB->get_records('" . $this->table . "');\n";
        $file_in .= "	}\n";
        $file_in .= "\n	/**\n";
        $file_in .= "	  * Get records\n";
        $file_in .= "	 */\n";
        $file_in .= "	public function get_records() {\n";
        $file_in .= "	    return \$this->results;\n";
        $file_in .= "	}\n";
        $file_in .= "\n	/**\n";
        $file_in .= "	  * Array to be used for selects\n";
        $file_in .= "	  * Defaults used key = record id, value = name \n";
        $file_in .= "	  * Modify as required. \n";
        $file_in .= "	 */\n";
        $file_in .= "	public function get_select_array() {\n";
        $file_in .= "	    \$array = [\n";
        $file_in .= "	        '' => get_string('select', '" . $this->namespace . "')\n";
        $file_in .= "	      ];\n";
        $file_in .= "	      foreach(\$this->results as \$r) {\n";
        $file_in .= "	            \$array[\$r->id] = \$r->name;\n";
        $file_in .= "	      }\n";
        $file_in .= "	    return \$array;\n";
        $file_in .= "	}\n";
        $file_in .= "\n}";

        $this->SaveFile($this->classname . "s.php", $file_in);
    }


    /**
     * Create function Get_var_name into your class
     *
     * @param string $file
     */
    private function CreateFunctionGetters($file)
    {
        for ($i = 0; $i != $this->classe_name[$this->num_of_tables]['total_vars']; $i++) {
            $file .= "\n	/**
	 * @return " . $this->classe_variables[$i]['var_name'] . " - " . $this->classe_variables[$i]['length'] . "
	 */";
            $file .= "\n	public function get_" . $this->classe_variables[$i]['var_name'] . "(){\n";
            $file .= "		return \$this->" . $this->classe_variables[$i]['var_name'] . ";\n";
            $file .= "	}\n";
        }
        return $file;
    }


    /**
     * Create function Set_var_name into your class
     * You can�t set Keys
     *
     * @param string $file
     */
    private function CreateFunctionSetters($file)
    {
        for ($i = 0; $i != $this->classe_name[$this->num_of_tables]['total_vars']; $i++) {
            if ($this->classe_variables[$i]['var_name'] != 1) {
                $file .= "\n	/**
	 * @param Type: " . $this->classe_variables[$i]['length'] . "
	 */";
                $file .= "\n	public function set_" . $this->classe_variables[$i]['var_name'] . "($" . $this->classe_variables[$i]['var_name'] . "){\n";
                $file .= "		\$this->" . $this->classe_variables[$i]['var_name'] . " = $" . $this->classe_variables[$i]['var_name'] . ";\n";
                $file .= "	}\n";
            }
        }
        return $file;
    }


    /**
     * Create function Save Active row, just to update
     *
     * @param string $file
     */
    private function CreateFunctionInsertRecord($file_in)
    {
        $file_in .= "\n    /**
     * Insert record into selected table
     * @global \moodle_database \$DB
     * @global \stdClass \$USER
     * @param object \$data
     */";
        $file_in .= "\n	public function insert_record(\$data){\n";
        $file_in .= "		global \$DB, \$USER;\n";

        $file_in .= "\n		if (!isset(\$data->timecreated)) {\n";
        $file_in .= "		    \$data->timecreated = time();\n";
        $file_in .= "		}\n";
        $file_in .= "\n		if (!isset(\$data->imemodified)) {\n";
        $file_in .= "		    \$data->timemodified = time();\n";
        $file_in .= "		}\n";
        $file_in .= "\n		//Set user\n";
        $file_in .= "		\$data->usermodified = \$USER->id;\n";
        $file_in .= "\n		\$id = \$DB->insert_record(\$this->table, \$data);\n";
        $file_in .= "\n		return \$id;\n";
        $file_in .= "	}\n";
        return $file_in;
    }

    /**
     * Create function Save Active row, just to update
     *
     * @param string $file
     */
    private function CreateFunctionUpdateRecord($file_in)
    {
        $file_in .= "\n    /**
     * Update record into selected table
     * @global \moodle_database \$DB
     * @global \stdClass \$USER
     * @param object \$data
     */";
        $file_in .= "\n	public function update_record(\$data){\n";
        $file_in .= "		global \$DB, \$USER;\n";
        $file_in .= "\n		if (!isset(\$data->timemodified)) {\n";
        $file_in .= "		    \$data->timemodified = time();\n";
        $file_in .= "		}\n";
        $file_in .= "\n		//Set user\n";
        $file_in .= "		\$data->usermodified = \$USER->id;\n";
        $file_in .= "\n		\$id = \$DB->update_record(\$this->table, \$data);\n";
        $file_in .= "\n		return \$id;\n";
        $file_in .= "	}\n";
        return $file_in;
    }


    /**
     * Create function Delete a row from key
     *
     * @param string $file
     */
    private function CreateFunctionDeleteRecord($file_in)
    {
        $file_in .= "\n\n    /**
     * Delete the row 
     *
     * @global \moodle_database \$DB
     *
     */";
        $file_in .= "\n	public function delete_record(){\n";
        $file_in .= "	    global \$DB;\n";
        $file_in .= "		\$DB->delete_records(\$this->table,['id' => \$this->id]);\n";
        $file_in .= "	}\n";
        return $file_in;
    }


    /**
     * Create function Load row into var by using a key
     *
     * @param string $file
     */
    private function CreateFunctionGetRecord($file_in)
    {
        $file_in .= "\n    /**
     * Get record
     *
     * @global \moodle_database \$DB
     * 
     */";
        $file_in .= "\n	public function get_record(){\n";
        $file_in .= "	    global \$DB;\n";
        $file_in .= "	    \$result = \$DB->get_record(\$this->table, ['id' => \$this->id]);\n";
        $file_in .= "	    return  \$result;\n";
        $file_in .= "\n	}";
        return $file_in;
    }


    /**
     * Create class vars with type coments
     *
     * @param string $file
     */
    private function CreateVars($file)
    {
        $this->seters == true ? $type = "private" : $type = "public";

        for ($i = 0; $i != $this->classe_name[$this->num_of_tables]['total_vars']; $i++) {
            $file .= "\n	/**\n";
            $file .= "	 *\n";
            if (strstr($this->classe_variables[$i]['type'], 'int')) {
                $file .= "	 *@var int\n";
            } else {
                $file .= "	 *@var string\n";
            }
            $file .= "	 */\n";
            $file .= "	$type $" . $this->classe_variables[$i]['var_name'] . ";\n";
            if ($this->classe_variables[$i]['var_name'] == 'timecreated') {
                $file .= "\n	/**\n";
                $file .= "	 *\n";
                $file .= "	 *@var string\n";
                $file .= "	 */\n";
                $file .= "	$type $" . $this->classe_variables[$i]['var_name'] . "_hr;\n";
            }
            if ($this->classe_variables[$i]['var_name'] == 'timemodified') {
                $file .= "\n	/**\n";
                $file .= "	 *\n";
                $file .= "	 *@var string\n";
                $file .= "	 */\n";
                $file .= "	$type $" . $this->classe_variables[$i]['var_name'] . "_hr;\n";
            }

        }
        $file .= "\n	/**\n";
        $file .= "	 *\n";
        $file .= "	 *@var string\n";
        $file .= "	 */\n";
        $file .= "	$type \$table;\n";

        return $file;
    }


    /**
     * Create function Construct
     *
     * @param string $file
     */
    private function CreateConstruct($file)
    {
        $file .= "\n\n    /**
     *  
     *
     */";
        $file .= "\n	public function __construct(\$id = 0){\n";
        $file .= "  	global \$CFG, \$DB, \$DB;\n";
        $file .= "\n		\$this->table = '" . $this->table . "';\n";
        $file .= "\n		parent::set_table(\$this->table);\n";
        $file .= "\n      if (\$id) {\n";
        $file .= "         \$this->id = \$id;\n";
        $file .= "         parent::set_id(\$this->id);\n";
        $file .= "         \$result = \$this->get_record(\$this->table, \$this->id);\n";
        $file .= "      } else {\n";
        $file .= "        \$result = new \stdClass();\n";
        $file .= "         \$this->id = 0;\n";
        $file .= "         parent::set_id(\$this->id);\n";
        $file .= "      }\n\n";

        for ($i = 0; $i != $this->classe_name[$this->num_of_tables]['total_vars']; $i++) {
            if ($this->classe_variables[$i]['key'] != 1) {
                if (strstr($this->classe_variables[$i]['type'], 'int')) {
                    $value = '0';
                } else {
                    $value = "''";
                }
                $file .= "		\$this->" . $this->classe_variables[$i]['var_name'] . " = \$result->" . $this->classe_variables[$i]['var_name'] . " ?? $value;\n";
                if ($this->classe_variables[$i]['var_name'] == 'timecreated') {
                    $file .= "          \$this->timecreated_hr = '';\n";
                    $file .= "          if (\$this->timecreated) {\n";
                    $file .= "		        \$this->" . $this->classe_variables[$i]['var_name'] . "_hr = userdate(\$result->" . $this->classe_variables[$i]['var_name'] . ",get_string('strftimedate'));\n";
                    $file .= "          }\n";
                }
                if ($this->classe_variables[$i]['var_name'] == 'timemodified') {
                    $file .= "      \$this->timemodified_hr = '';\n";
                    $file .= "          if (\$this->timemodified) {\n";
                    $file .= "		        \$this->" . $this->classe_variables[$i]['var_name'] . "_hr = userdate(\$result->" . $this->classe_variables[$i]['var_name'] . ",get_string('strftimedate'));\n";
                    $file .= "          }\n";
                }
            }
        }

        $file .= "	}\n";
        return $file;
    }


    /**
     * Return a key of the last table in var_array
     *
     * @param string $file
     */
    private function GetKeyOf_table()
    {
        for ($z = 0; $z != $this->classe_name[$this->num_of_tables]['total_vars']; $z++) {
            if ($this->classe_variables[$z]['key'] == 1) return $z;
        }
        return 0;
    }


    /**
     * Create file and put inside your code
     *
     * @param string $filename
     * @param string $text
     *
     */
    private function SaveFile($filename, $text)
    {
        global $CFG;
        if ($this->VerifyDirectory()) {
            $file = fopen($this->path . "/" . $this->time . "/" . $filename, "w");
            fwrite($file, $text);
            fclose($file);
        }
    }

    private function ZipPackage()
    {
        $zip = new ZipArchive();
        $filename = $this->path . '/' . $this->time . '/' . $this->classname . '.zip';
        if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
            exit("cannot open <$filename>\n");
        }
//        print_object(file_get_contents($this->path . '/' . $this->time . '/' . $this->classname . '.php'));
        // Add crud file
        if (file_exists($this->path . '/' . $this->time . '/' .  'crud.php')) {
            $zip->addFile($this->path . '/' . $this->time . '/' . 'crud.php',  'crud.php');
        }
        // Add singular class
        if (file_exists($this->path . '/' . $this->time . '/' . $this->classname . '.php')) {
            $zip->addFile($this->path . '/' . $this->time . '/' . $this->classname . '.php', $this->classname . '.php');
        }
        // Add plural class
        if (file_exists($this->path . '/' . $this->time . '/' . $this->classname . 's.php')) {
            $zip->addFile($this->path . '/' . $this->time . '/' . $this->classname . 's.php', $this->classname . 's.php');
        }
        $zip->close();

        return $filename;
        // Force file download
//        header("Content-Description: File Transfer");
//        header("Content-Type: application/octet-stream");
//        header("Content-Disposition: attachment; filename=\"". basename($filename) ."\"");
//
//        readfile ($filename);
//        redirect($CFG->wwwroot . '/admin/tool/orm/');
//        exit();
    }


    /**
     * Create Directory to save the files if don�t exist
     *
     */
    private function VerifyDirectory()
    {
        global $CFG;

        if (!is_dir($this->path)) {
            mkdir($path, 0777, true);
        }

        if (!is_dir($this->path . '/' . $this->time)) {
            mkdir($this->path . '/' . $this->time, 0777, true);
        }
        return true;
    }


    /**
     * You can�t change this!
     *
     */
    private function Copyright()
    {
        global $USER;
        return "/*
 * Author: " . fullname($USER) . "
 * Create Date: " . date("j-m-Y") . "
 * License: LGPL 
 * 
 */";
    }


    /**
     * @return Total of tables
     */
    public function getNum_of_tables()
    {
        return $this->num_of_tables;
    }


    /**
     * @return Total of variables
     */
    public function getNum_of_total_variables()
    {
        return $this->num_of_total_variables;
    }


    /**
     * @param True to active Getters Functions, or false
     */
    public function setGeters($geters)
    {
        $this->geters = $geters;
    }


    /**
     * @param True to active Function query("Select * from table where id=$i), or false
     */
    public function setLoad_row_from_key($load_row_from_key)
    {
        $this->load_row_from_key = $load_row_from_key;
    }


    /**
     * @param boolean $save_active_row
     */
    public function setSave_active_row($save_active_row)
    {
        $this->save_active_row = $save_active_row;
    }


    /**
     * @param boolean $save_active_row_as_new
     */
    public function setSave_active_row_as_new($save_active_row_as_new)
    {
        $this->save_active_row_as_new = $save_active_row_as_new;
    }


    /**
     * @param boolean $seters
     */
    public function setSeters($seters)
    {
        $this->seters = $seters;
    }

    /**
     * @return array table names and total of variables
     */
    public function getClasse_name()
    {
        return $this->classe_name;
    }


    /**
     * @param boolean $construct
     */
    public function setConstruct($construct)
    {
        $this->construct = $construct;
    }

    /**
     * @param boolean $delete_row_from_key
     */
    public function setDelete_row_from_key($delete_row_from_key)
    {
        $this->delete_row_from_key = $delete_row_from_key;
    }

    /**
     * @param boolean $Order_Keys
     */
    public function setOrder_Keys($Order_Keys)
    {
        $this->Order_Keys = $Order_Keys;
    }


    public function endMySQL_to_PHP()
    {
        $this->connection->close();
    }

}

?>