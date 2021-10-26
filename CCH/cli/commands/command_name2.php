<?php
/**
 *  ласс команды command_name2
 *
 * ≈ще один вариант демонстрирующий множественную реализацию команд,
 * возвращает сериализованные аргументы п параметры.
 *
 * @copyright 2021 Sevastyan Stepanov <seba13@mail.ru>
 * @package cli_commands_handler
 */
class command_name2 implements \command_base
{
    /**
     * 
     * {@inheritDoc}
     * @see command_base::process()
     */
    public function process($args, $params, $command)
    {
        return [json_encode($args), json_encode($params)];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see command_base::description()
     */
    public function description()
    {
        $description = 'Praesent in mauris eu tortor porttitor accumsan. 
                        Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, 
                        vel sagittis velit mauris vel metus. Aenean fermentum risus id tortor. 
                        Integer imperdiet lectus quis justo. Integer tempor. 
                        Vivamus ac urna vel leo pretium';
        return $description;
    }
}

