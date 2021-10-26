<?php
/**
 * Класс команды command_name
 * 
 * Реализовано согласно техническому заданию.
 * 
 * @copyright 2021 Sevastyan Stepanov <seba13@mail.ru>
 * @package cli_commands_handler
 */
class command_name implements \command_base
{
    /**
     * Дерево параметров
     * 
     * @var array
     */
    private $treedata = [];

    /**
     * 
     * {@inheritDoc}
     * @see command_base::process()
     */
    public function process($args, $params, $command)
    {
        $data = [
            'Called command: ' . $command,
            ''
        ];
        if ($args) {
            $data[] = 'Arguments:';
            foreach ($args as $arg) {
                $data[] = '   -' . $arg;
            }
        }
        if ($params) {
            $data[] = '';
            $data[] = 'Options:';
            $this->tree($params);
            $data = array_merge($data, $this->treedata);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     * @see command_base::description()
     */
    public function description()
    {
        $description = 'Morbi a metus. Phasellus enim erat, vestibulum vel, aliquam a, posuere eu, velit. 
                        Nullam sapien sem, ornare ac, nonummy non, lobortis a, enim. 
                        Nunc tincidunt ante vitae massa. Duis ante orci, molestie vitae, 
                        vehicula venenatis, tincidunt ac, pede. Nulla accumsan, elit sit';
        return $description;
    }
    
    /**
     * Рекурсивно строит дерево параметров
     * 
     * @param object $params
     * @param number $level определяет отступ в зависимости от глубины вложенности параметров
     */
    private function tree($params, $level = 0) {
        $tab = function($level) {
            $offset = '';
            for ($i = 0; $i < $level; $i++) {
                $offset .= '   ';
            }
            $offset .= '-';
            return $offset;
        };
        
        foreach ($params as $paramname => $parmvalue) {
            $this->treedata[] = $tab($level) . $paramname;
            foreach ($parmvalue as $value) {
                if (is_object($value)) {
                    $this->tree($value, $level + 1);
                } else {
                    $this->treedata[] = $tab($level + 1) . $value;
                }
            }
        } 
    }
}

