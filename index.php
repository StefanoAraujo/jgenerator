<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

class JmoduleCreator {

    public $msname = null;
    public $mname = null;
    public $mcreationdate = null;
    public $mversion = null;
    public $mdescr = null;
    public $mauthor = null;
    public $mauthoremail = null;
    public $mauthorurl = null;
    public $mcopyright = null;
    public $mlicense = null;
    public $mhelpername = null;
    public $zipfiles = array();

    function __construct() {
        $this->msname = $_POST['msname'];
        $this->mname = $_POST['mname'];
        $this->mcreationdate = $_POST['mcreationdate'];
        $this->mversion = $_POST['mversion'];
        $this->mdescr = $_POST['mdescr'];
        $this->mauthor = $_POST['mauthor'];
        $this->mauthoremail = $_POST['mauthoremail'];
        $this->mauthorurl = $_POST['mauthorurl'];
        $this->mcopyright = $_POST['mcopyright'];
        $this->mlicense = $_POST['mlicense'];
        $this->mhelpername = ucfirst(trim(str_replace('_', '', $this->msname))) . 'Helper';
    }

    function generateMainPhp() {
        $php_content = array();
        $php_content [] = "<?php";
        $php_content [] = "defined('_JEXEC') or die;";
        $php_content [] = "require_once dirname(__FILE__).'/helper.php';";
        $php_content [] = '//$items = ' . $this->mhelpername . '::getItems($params);';
        $php_content [] = '//if (!count($items)) {return false;}';
        $php_content [] = '$moduleclass_sfx = htmlspecialchars($params->get("moduleclass_sfx"));';
        $php_content [] = 'require JModuleHelper::getLayoutPath("' . $this->msname . '",$params->get("layout", "default"));';
        $php_content [] = '';
        $php_str = implode("\r\n", $php_content);
        return $php_str;
    }

    function generateHelperPhp() {
        $php_content = array();
        $php_content [] = "<?php";
        $php_content [] = "defined('_JEXEC') or die;";
        $php_content [] = "class ". $this->mhelpername." ";
        $php_content [] = '{';
        $php_content [] = 'public static function getItems($params)';
        $php_content [] = '{';
        $php_content [] = 'return null;	';
        $php_content [] = '}';
        $php_content [] = '}';
        $php_str = implode("\r\n", $php_content);
        return $php_str;
    }

    function generateXml() {
        $xml_content = array();
        $xml_content [] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml_content [] = '<extension type="module"	version="1.6.0"	client="site" method="upgrade">';
        $xml_content [] = '<name>' . $this->mname . '</name>';
        $xml_content [] = '<creationDate>' . $this->mcreationdate . '</creationDate>';
        $xml_content [] = '<author>' . $this->mauthor . '</author>';
        $xml_content [] = '<authorEmail>' . $this->mauthoremail . '</authorEmail>';
        $xml_content [] = '<authorUrl>' . $this->mauthorurl . '</authorUrl>';
        $xml_content [] = '<copyright>' . $this->mcopyright . '</copyright>';
        $xml_content [] = '<license>' . $this->mlicense . '</license>';
        $xml_content [] = '<version>' . $this->mversion . '</version>';
        $xml_content [] = '<description>' . $this->mdescr . '</description>';
        $xml_content [] = '<files>';
        $xml_content [] = '<filename module="' . $this->msname . '">' . $this->msname . '.php</filename>';
        $xml_content [] = '<folder>tmpl</folder>';
        $xml_content [] = '<filename>helper.php</filename>';
        $xml_content [] = '<filename>index.html</filename>';
        $xml_content [] = '<filename>' . $this->msname . '.xml</filename>';
        $xml_content [] = '</files>';
        $xml_content [] = '<languages>
        <language tag="en-GB">language/en-GB/en-GB.' . $this->msname . '.ini</language>
        <language tag="en-GB">language/en-GB/en-GB.' . $this->msname . '.sys.ini</language>
	</languages>';
        $xml_content [] = '<config>
		<fields name="params">
			<fieldset name="basic">
			<field name="moduleclass_sfx" type="text" default="" label="Module Class Suffix" description="PARAMMODULECLASSSUFFIX" />
			</fieldset>			
		</fields>
	</config>';
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
            $zip_name = $this->msname . ".zip";
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
    
    function generateTmplFolder()
    {
        if (!file_exists("tmpl"))mkdir("tmpl");
        $php_content = array();
        $php_content [] = "<?php";
        $php_content [] = "defined('_JEXEC') or die;";
        $php_content [] = "";
        $php_str = implode("\r\n", $php_content);
       
        $this->addToZip($this->createFile('tmpl/default.php', $php_str));
        $this->addToZip($this->createFile('tmpl/index.html', '<html><body></body></html>'));
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
        $this->addToZip($this->createFile('language/en-GB/en-GB.'.$this->msname.'.ini', '; Note : All ini files need to be saved as UTF-8'));
        $this->addToZip($this->createFile('language/en-GB/en-GB.'.$this->msname.'.sys.ini', '; Note : All ini files need to be saved as UTF-8'));
    }
    
    function deleteTmpFolders() 
    {
        if (file_exists("language/en-GB")) rmdir ("language/en-GB");
        if (file_exists("language")) rmdir ("language");
        if (file_exists("tmpl")) rmdir ("tmpl");
    }

}

if (isset($_POST['msname'])) {
    $new_module = new JmoduleCreator();
    $new_module->addToZip($new_module->createFile($new_module->msname . '.xml', $new_module->generateXml()));
    $new_module->addToZip($new_module->createFile($new_module->msname . '.php', $new_module->generateMainPhp()));
    $new_module->addToZip($new_module->createFile('helper.php', $new_module->generateHelperPhp()));
    $new_module->generateTmplFolder();
    $new_module->generateLangFolder();
    $new_module->createAndSaveZip();
    $new_module->deleteTmpFolders();
}
?>
<html>
    <head>
        <link rel="stylesheet" href="style/css/bootstrap.min.css">
        <link rel="stylesheet" href="style/css/bootstrap-theme.min.css">
        <script src="style/js/bootstrap.min.js"></script>
        <title>J! module creator</title>
    </head>
    <body>
        <form method="post" action="index.php" name="subform"  class="form"/>
        <div class="jumbotron"> 
            <h1>J! module creator:</h1>
            <table width="50%" class="table table-striped table-hover">
                <tr>
                    <td>System name of module:</td>
                    <td><input class="form-control" type="text" value="mod_" name="msname" size="45" /></td>
                </tr>
                <tr>
                    <td>Title(Name) of module:</td>
                    <td><input class="form-control" type="text" value="" name="mname" size="45" /></td>
                </tr>
                <tr>
                    <td>CreationDate:</td>
                    <td><input class="form-control" type="text" value="<?php echo date('F Y'); ?>" name="mcreationdate" size="45" /></td>
                </tr>
                <tr>
                    <td>Version:</td>
                    <td><input class="form-control" type="text" value="1.0.0" name="mversion" size="45" /></td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><textarea class="form-control" name="mdescr"></textarea></td>
                </tr>
                <tr>
                    <td>Author:</td>
                    <td><input class="form-control" type="text" value="" name="mauthor" size="45" /></td>
                </tr>
                <tr>
                    <td>AuthorEmail:</td>
                    <td><input class="form-control" type="text" value="" name="mauthoremail" size="45" /></td>
                </tr>
                <tr>
                    <td>AuthorUrl:</td>
                    <td><input class="form-control" type="text" value="" name="mauthorurl" size="45" /></td>
                </tr>
                <tr>
                    <td>Copyright:</td>
                    <td><input class="form-control" type="text" value="Copyright 2010 - 2014. All rights reserved" name="mcopyright" size="45" /></td>
                </tr>
                <tr>
                    <td>License:</td>
                    <td><input class="form-control" type="text" value="GNU" name="mlicense" size="45" /></td>
                </tr>
            </table>
            <button class="btn btn-primary btn-lg" type="submit" >Generate new module</button>
        </div>
    </form>
        <div class="btn btn-primary btn-xs pull-right " disabled="true">Created by SMT</div>
</body>
</html>