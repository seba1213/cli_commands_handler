<?php
/**
 * Cli Commands Handler
 *
 * Интерфейс команд
 *
 * @copyright 2021 Sevastyan Stepanov <seba13@mail.ru>
 * @package cli_commands_handler
 */
interface command_base
{
    /**
     * Описание команды
     */
    public function description();
    
    /**
     * Обработчик команды, должен возвращать сроку или массив строк для отображения
     * пользователю после исполнения собственной логики.
     * 
     * @param array $args аргументы
     * @param object $params параметры
     * @param string $command название исполняекой комманды
     */
    public function process(array $args, object $params, string $command);
    
}

