<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

class JpluginCreator {

    public $fullsname = null;
    public $ptype = null;
    public $sname = null;
    public $name = null;
    public $creationdate = null;
    public $version = null;
    public $descr = null;
    public $author = null;
    public $authoremail = null;
    public $authorurl = null;
    public $copyright = null;
    public $license = null;
    //public $helpername = null;
    public $ipfiles = array();

    function __construct() {
        $this->fullsname = 'plg_'.$_POST['ptype'].'_'.$_POST['sname'];
        $this->ptype = $_POST['ptype'];
        $this->sname = $_POST['sname'];
        $this->name = $_POST['name'];
        $this->creationdate = $_POST['creationdate'];
        $this->version = $_POST['version'];
        $this->descr = $_POST['descr'];
        $this->author = $_POST['author'];
        $this->authoremail = $_POST['authoremail'];
        $this->authorurl = $_POST['authorurl'];
        $this->copyright = $_POST['copyright'];
        $this->license = $_POST['license'];
        //$this->params_names = $_POST['params_names'];
        //$this->params_labels = $_POST['params_labels'];
       // $this->helpername = ucfirst(trim(str_replace('_', '', $this->sname))) . 'Helper';
    }

    function generateMainPhp() {
        $php_content = array();
        $php_content [] = "<?php";
        $php_content [] = "defined('_JEXEC') or die;";
        $php_content [] = "class Plg".ucfirst($this->ptype).ucfirst($this->sname)." extends JPlugin";
        $php_content [] = "{";
        $php_content [] = "}";
        $php_content [] = '';
        $php_str = implode("\r\n", $php_content);
        return $php_str;
    }

    function generateXml() {
        $xml_content = array();
        $xml_content [] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml_content [] = '<extension type="plugin"	version="1.6.0" group="'.$this->ptype.'">';
        $xml_content [] = '<name>' . $this->name . '</name>';
        $xml_content [] = '<creationDate>' . $this->creationdate . '</creationDate>';
        $xml_content [] = '<author>' . $this->author . '</author>';
        $xml_content [] = '<authorEmail>' . $this->authoremail . '</authorEmail>';
        $xml_content [] = '<authorUrl>' . $this->authorurl . '</authorUrl>';
        $xml_content [] = '<copyright>' . $this->copyright . '</copyright>';
        $xml_content [] = '<license>' . $this->license . '</license>';
        $xml_content [] = '<version>' . $this->version . '</version>';
        $xml_content [] = '<description>' . $this->descr . '</description>';
        $xml_content [] = '<files>';
        $xml_content [] = '<filename  plugin="' . $this->sname . '">' . $this->sname . '.php</filename>';
        $xml_content [] = '<filename>index.html</filename>';
        $xml_content [] = '<filename>' . $this->sname . '.xml</filename>';
        $xml_content [] = '</files>';
        $xml_content [] = '<languages>
        <language tag="en-GB">language/en-GB/en-GB.' . $this->fullsname . '.ini</language>
        <language tag="en-GB">language/en-GB/en-GB.' . $this->fullsname . '.sys.ini</language>
	</languages>';
        /*
        if (sizeof($this->params_names))
        {
            $xml_content [] = '<config>';
            $xml_content [] = '<fields name="params">';
            $xml_content [] = '<fieldset name="basic">';
            foreach ($this->params_names as $k=> $param_name)
            {
                if ($param_name)
                {
                    $xml_content [] = '<field name="'.$param_name.'" type="text" default="" label="'.$this->params_labels[$k].'" description="" />';
                }
            }
            $xml_content [] = '</fieldset>';
            $xml_content [] = '</fields>';
            $xml_content [] = '</config>';
        }
        */
        $xml_content [] = '</extension>';
        $xml_str = implode("\r\n", $xml_content);
        return $xml_str;
    }

    function createFile($filename = '', $content = '') {
        $fp = fopen($filename, "w");
        $wresult = fwrite($fp, $content);
        fclose($fp);
        return $filename;
    }

    function addToZip($filename = '') {
        $this->zipfiles[] = $filename;
    }

    function createAndSaveZip() {
        if (extension_loaded('zip')) {
            $zip = new ZipArchive();
            $zip_name = $this->fullsname . ".zip";
            if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== TRUE) {
                return false;
            }
            if (sizeof($this->zipfiles))
                foreach ($this->zipfiles as $zfile) {
                    $zip->addFile($zfile);
                }
            $zip->close();
            if (file_exists($zip_name)) {
                header('Content-type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zip_name . '"');
                readfile($zip_name);
                unlink($zip_name);

                if (sizeof($this->zipfiles))
                    foreach ($this->zipfiles as $zfile) {
                        unlink($zfile);
                    }
            }
        }
        else
            return false;
    }
    

    function generateLangFolder()
    {
        if (!file_exists("language"))
        {
            mkdir("language");
            mkdir("language/en-GB");
        }
       
        $this->addToZip($this->createFile('language/en-GB/index.html', '<html><body></body></html>'));
        $this->addToZip($this->createFile('language/index.html', '<html><body></body></html>'));
        $this->addToZip($this->createFile('language/en-GB/en-GB.'.$this->fullsname.'.ini', '; Note : All ini files need to be saved as UTF-8'));
        $this->addToZip($this->createFile('language/en-GB/en-GB.'.$this->fullsname.'.sys.ini', '; Note : All ini files need to be saved as UTF-8'));
    }
    
    function deleteTmpFolders() 
    {
        if (file_exists("language/en-GB")) rmdir ("language/en-GB");
        if (file_exists("language")) rmdir ("language");
        if (file_exists("tmpl")) rmdir ("tmpl");
    }

}

if (isset($_POST['sname'])) {
    $new_plugin = new JpluginCreator();
    $new_plugin->addToZip($new_plugin->createFile($new_plugin->sname . '.xml', $new_plugin->generateXml()));
    $new_plugin->addToZip($new_plugin->createFile($new_plugin->sname . '.php', $new_plugin->generateMainPhp()));
    $new_plugin->addToZip($new_plugin->createFile('index.html', '<html><body></body></html>'));
    $new_plugin->generateLangFolder();
    $new_plugin->createAndSaveZip();
    $new_plugin->deleteTmpFolders();
}
?>
<html>
    <head>
        <link rel="stylesheet" href="style/css/bootstrap.min.css">
        <link rel="stylesheet" href="style/css/bootstrap-theme.min.css">
        <script src="style/js/bootstrap.min.js"></script>
        <title>J! plugin creator</title>
    </head>
    <body>
        <form method="post" action="plugin.php" name="subform"  class="form"/>
        <div class="jumbotron"> 
            <h1>J! plugin creator:</h1>
            <table width="50%" class="table table-striped table-hover">
                <tr>
                    <td>System name of plugin:</td>
                    <td>plg_
                        <select name="ptype">
                            <option value="system">system</option>
                            <option value="user">user</option>
                            <option value="content">content</option>
                        </select>
                        _
                        <input class="form-control2" type="text" value="" name="sname" size="45" /></td>
                </tr>
                <tr>
                    <td>Title(Name) of plugin:</td>
                    <td><input class="form-control" type="text" value="" name="name" size="45" /></td>
                </tr>
                <tr>
                    <td>CreationDate:</td>
                    <td><input class="form-control" type="text" value="<?php echo date('F Y'); ?>" name="creationdate" size="45" /></td>
                </tr>
                <tr>
                    <td>Version:</td>
                    <td><input class="form-control" type="text" value="1.0.0" name="version" size="45" /></td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><textarea class="form-control" name="descr"></textarea></td>
                </tr>
                <tr>
                    <td>Author:</td>
                    <td><input class="form-control" type="text" value="" name="author" size="45" /></td>
                </tr>
                <tr>
                    <td>AuthorEmail:</td>
                    <td><input class="form-control" type="text" value="" name="authoremail" size="45" /></td>
                </tr>
                <tr>
                    <td>AuthorUrl:</td>
                    <td><input class="form-control" type="text" value="" name="authorurl" size="45" /></td>
                </tr>
                <tr>
                    <td>Copyright:</td>
                    <td><input class="form-control" type="text" value="Copyright 2010 - 2014. All rights reserved" name="copyright" size="45" /></td>
                </tr>
                <tr>
                    <td>License:</td>
                    <td><input class="form-control" type="text" value="GNU" name="license" size="45" /></td>
                </tr>
            <!--
             <tr>
                   <td>
                       J! plugin params (text):
                 </td>
                   <td>
                       !todo
                   </td>
               </tr>
               -->
            </table>
            <button class="btn btn-primary btn-lg" type="submit" >Generate new plugin</button>
        </div>
    </form>
        <div class="btn btn-primary btn-xs pull-right " disabled="true">Created by SMT</div>
</body>
</html>