<?php
/**
 * Cli Commands Handler
 *
 * ��������� ������
 *
 * @copyright 2021 Sevastyan Stepanov <seba13@mail.ru>
 * @package cli_commands_handler
 */
interface command_base
{
    /**
     * �������� �������
     */
    public function description();
    
    /**
     * ���������� �������, ������ ���������� ����� ��� ������ ����� ��� �����������
     * ������������ ����� ���������� ����������� ������.
     * 
     * @param array $args ���������
     * @param object $params ���������
     * @param string $command �������� ����������� ��������
     */
    public function process(array $args, object $params, string $command);
    
}

