<?php
require_once  __DIR__ . '/command_base.php';

/**
 * Cli Commands Handler
 * 
 * ������������ ��������� �����-������ (I/O) ��� ������ � �������, 
 * ������������ ����������� ����������� ����������� �������
 * 
 * @copyright 2021 Sevastyan Stepanov <seba13@mail.ru>
 * @package cli_commands_handler
 */
class cli_commands_handler
{
    /**
     * ���������
     * 
     * @var object
     */
    protected $params;
    
    /**
     * ��������
     * 
     * @var array
     */
    protected $args = [];
    
    /**
     * ����������� �������
     * 
     * @var string
     */
    protected $command = '';

    /**
     * 
     * @param string $path - ���� �� ����� � �������� ������
     * @param string $description - ���������������� �������� ��� ����������, 
     * ������������ ���� ���������� ������� ��� �������� ���������� �������.
     */
    public function __construct(string $path, string $description = null)
    {
        $this->description = $description;
        $this->params = new stdClass();
        // �������� ���������� ������� ������� � ��������� ����������� �����
        $commands = $this->get_command_insts_by_patch($path);
        // ��������� ���������� ��������� ������� ����������, 
        // ��������� ��������������� �������� ������
        try {
            $this->process_params($commands);
        } catch (\Exception $e) {
            $this->cli_writeln($e->getMessage());
            exit(0);
        }
        // ��������� ������ ����������
        $cliresponse = $commands[$this->command]->process(
            $this->args, $this->params, $this->command
            );
        // ������ ���������� ����������
        if (is_array($cliresponse)) {
            foreach ($cliresponse as $str) {
                $this->cli_writeln((string)$str); 
            }
        } else {
            $this->cli_writeln((string)$cliresponse);
        }
    }
    
    /**
     * ������� �������� ���� ��� ���������� ������
     * 
     * @param array $commands
     * @param string $commandstr
     * @throws \Exception
     */
    private function print_commands_descriptions(array $commands, string $commandstr = null) {
        if (is_null($commandstr)) {
            // ����� �������� ����������
            if ($this->description) {
                $this->cli_writeln((string)$this->description);
            } else {
                $this->header_def();
            }
            foreach ($commands as $commandinst) {
                $string = get_class($commandinst) . ' - ' . $commandinst->description();
                $this->cli_writeln($string);
            }
        } else { // �������� ���������� �������, ����� �������� ������ �� ��������.
            if (!array_key_exists($commandstr, $commands)) {
                throw new \Exception("There is no command '$commandstr'");
            }
            $description = $commands[$commandstr]->description();
            $this->cli_writeln($commandstr . ' - ' . $description);
        }
    }
    
    /**
     * ���������� ������� ������ � ��������� ����������
     * 
     * @param string $path
     * @return command_base[]
     */
    private function get_command_insts_by_patch(string $path) {
        $commands = [];
        if ($handle = opendir($path)) {
            while ($entry = readdir($handle)) {
                $filepath = $path . "/" . $entry;
                if (is_file($filepath) && strpos($entry, '.php') !== false) {
                    require_once($filepath);
                    $classname = substr($entry, 0, strpos($entry, '.php'));
                    if (class_exists($classname)) {
                        $command = new $classname();
                        if ($command instanceof command_base) {
                            $commands[$classname] = $command;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $commands;
    }   
    
    /**
     * ���������� ���������� � ����������
     * 
     * @param array $commands
     * @throws \Exception
     */
    private function process_params(array $commands) {
        if (empty($commands)) {
            throw new \Exception('Commands for application are not defined');
        }
        if (empty($_SERVER['argv'])) {
            throw new \Exception('Error of the server variable "argv"');
        }
        $rawoptions = $_SERVER['argv'];
        // ���������� ���������� �������
        $this->command = $this->exclude_command_from_raw($rawoptions);
        if ($this->command === false) {
            $this->print_commands_descriptions($commands);
            exit(0);
        }
        if (! isset($rawoptions[2])) {
            throw new \Exception('Passed command has no arguments');
        }
        if ($rawoptions[2] == '{help}') {
            $this->print_commands_descriptions($commands, $this->command);
            exit(0);
        }
        // ���������� ��������� ���������� �� ����������
        foreach ($rawoptions as $rawoption) {
            $this->set_parameters_from_string($rawoption, $this->params);
        }  
    }

    /**
     * ���������� ��������� ���������� ��������� � ���������� ������� � �������� ������
     * 
     * @param string $rawoption
     * @param object $params
     */
    private function set_parameters_from_string(string $rawoption, $params) {
        $subparam = null;
        $matches = $submatches = [];
        // ������ ��������� ��������� ��������� � ���������
        $pattern = '/^\[([^=]+)=(.+)\]$/';
        if (preg_match($pattern, $rawoption, $matches)) {
            // �������� �������� �� �������� ��������� �������������
            if (preg_match($pattern, $matches[2], $submatches)) {
                // ��������� ������������� ������������ ����� �� ��������� �����
                foreach ($params->{$matches[1]} as $param) {
                    if (property_exists($param, $submatches[1])) {
                        $subparam = $param;
                        break;
                    }
                }
                if (is_null($subparam)) {// ����������� �����������, �������� �����
                    $subparam = $params->{$matches[1]}[] = new stdClass();
                }
                // �������� ����� ����������, �������� ������ ��� ���������� �� ������
                $this->set_parameters_from_string($matches[2], $subparam);
            } else {// �������� ��������� �� �������� �������������
                $params->{$matches[1]}[] = $matches[2];
            }
        } else { // ������� ��������, ���������� � ������� � ��������������� �������� ������
            $this->set_argumets_from_string($rawoption);
        }
    }
    
    /**
     * ���������� ��������� � ��������������� �������� ������
     * 
     * @param string $rawoption
     */
    private function set_argumets_from_string(string $rawoption) {
        $matches = [];
        // ���� �������� ��������� �� �� ������������ � �������, ������ ��
        if (preg_match('/{(.+)}/', $rawoption, $matches)) {
            $rawoption = $matches[1];
        }
        $this->args[] = $rawoption;
    }
    
    /**
     * �������� �� ������ ������� �������� �������, 
     * ����� ������� ��� ������������ �������
     * 
     * @param array $rawoptions
     * @throws \Exception
     * @return string ��� ����������� �������
     */
    private function exclude_command_from_raw(array &$rawoptions) {
        // �������� ��� ������������ ����� - ������ �� �������
        unset($rawoptions[0]);
        if (!isset($rawoptions[1])) {
            return false;
        }
        $command = $rawoptions[1];
        unset ($rawoptions[1]);
        // ������� ������������ ��������� ������, �������� �� ���������� �������, ����� ���������
        // ��� ������� �� �������� ���������� ��� ����������.
        if (!preg_match('/^([A-Za-z0-9_]*)$/', $command)) {
            throw new \Exception('The command is skipped or contains characters that are not supported');
        }
        return $command;
    }
    
    /**
     * ����� ����� � �����
     *
     * @param string $text
     * @param resource $stream �� ��������� ����� � STDOUT
     */
    public static function cli_write($text, $stream = STDOUT) {
        fwrite($stream, $text);
    }
    
    /**
     * ������� ����� �� ������� ������� ������ ����� ������
     *
     * @param string $text
     * @param resource $stream �� ��������� ����� � STDOUT
     */
    public static function cli_writeln($text, $stream=STDOUT) {
        self::cli_write($text . PHP_EOL, $stream);
    }
    
    /**
     * ��������)
     */
    private function header_def() {
        $t = '';
        $t .= " _____  _  _   _____                                _   _                    _  _             " . PHP_EOL;
        $t .= "/  __ \| |(_) /  __ \                              | | | |                  | || |            " . PHP_EOL;
        $t .= "| /  \/| | _  | /  \/  ___   _ __ ___   _ __ ___   | |_| |  __ _  _ __    __| || |  ___  _ __ " . PHP_EOL;
        $t .= "| |    | || | | |     / _ \ | '_ ` _ \ | '_ ` _ \  |  _  | / _` || '_ \  / _` || | / _ \| '__|" . PHP_EOL;
        $t .= "| \__/\| || | | \__/\| (_) || | | | | || | | | | | | | | || (_| || | | || (_| || ||  __/| |   " . PHP_EOL;
        $t .= " \____/|_||_|  \____/ \___/ |_| |_| |_||_| |_| |_| \_| |_/ \__,_||_| |_| \__,_||_| \___||_|   " . PHP_EOL;
        
        $t .= PHP_EOL . "Usage:" . PHP_EOL;
        $t .= "{arg} - single argument" . PHP_EOL;
        $t .= "{arg1,arg2,arg3} else {arg1} {arg2} {arg3} else {arg1,arg2} {arg3} - multiple arguments" . PHP_EOL;
        $t .= "[name=value] - parameter with a single value" . PHP_EOL;
        $t .= "[name={value1,value2,value3}] - parameter with multiple values" . PHP_EOL;
        
        $t .= PHP_EOL . "Examples:" . PHP_EOL;
        $t .= "php app.php command_name {arg} {arg1,arg2,arg3} [name={value1,value2,[name2={v1,v2}]}] {arg4} {arg5} [name3=value4]" . PHP_EOL;
        
        $t .= PHP_EOL . "App commands:";
        
        self::cli_writeln($t);
        
    } 
}

