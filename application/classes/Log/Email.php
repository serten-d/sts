<?php

defined('SYSPATH') OR die('No direct script access.');
/**
 * Email log writer. Send log information trough email.
 *
 * @author dariusz daniec
 */
class Log_Email extends Log_Writer
{
    /**
     * @var string Configuration table
     */
    protected $_config;

    /**
     * @var string config group
     */
    protected $_group;

    /**
     * @var string emaila subiect
     */
    protected $_title;

    /**
     * @var string script start time
     */
    protected $_time;

    /**
     * @var string header informations
     */
    protected $_header_message;

    /**
     * Creates a new email logger. 
     *
     * $writer = new Log_Email('log name', 'dev', $config);
     * where config include no dev key email configuration
     *
     * @param array $config config table
     * @return void
     */
    public function __construct($emailSubject, $group, $config)
    {
        $this->_header_message = '';
        $this->_config = $config;
        $this->_group = $group;
        $this->_title = $emailSubject;
        $this->_time = date('Y-m-d H:i:s');
    }
    
    /**
     * set e-mail additional informations placed on top of email
     * 
     * @param array|string $mHeader text pr table of texts to place on top of e-mail
     * @return \Log_Email
     */
    public function setHeader($mHeader)
    {
        if(is_array($mHeader))
        {
            $mHeader = implode("\n", $mHeader);
        }
        
        $this->_header_message = $mHeader . "\n";
        
        return $this;
    }

    /**
     * Writes each of the messages into the database table.
     *
     * $writer->write($messages);
     *
     * @param array $messages
     * @return void
     */
    public function write(array $messages)
    {

        $text = [];
        $config = $this->_config[$this->_group];

        $text[] = nl2br($this->_header_message);
        $text[] = 'Time start: ' . $this->_time;
        $text[] = 'Time end: ' . date('Y-m-d H:i:s');
        
        /*
         * Przechowuje informacje o uzytych statusach wiadomosci.
         * Nastepnie do title wiadomosci dodany zostanie prefix dla najnizszego 
         * numeru uzytego statusu (im nizszy tym wazniejszy status wiadomosci)
         * Dla notice i info nie zapisujemy poniewaz sa one tylko informacyjne.
         */
        $prefixesOfAllLogTypes = [];
        
        foreach ($messages as $message)
        {
            $additional = Arr::get($message, 'additional');

            $text[] = '===============================';
            $text[] = 'Level: ' . Arr::get($message, 'level');
            $text[] = 'Body: ' . Arr::get($message, 'body');
            
            $iMessageLevel = Arr::get($message, 'level');
            switch ($iMessageLevel)
            {
                case Log::WARNING:
                    $prefixesOfAllLogTypes[Log::WARNING] = 'Warning: ';
                    $this->_addTrace(
                            $text, 
                            Arr::get($message, 'trace'), 
                            Arr::get($message, 'file'), 
                            Arr::get($message, 'line'), 
                            Arr::get($message, 'class'), 
                            Arr::get($message, 'function'), 
                            $additional
                    );
                    break;
                case Log::ERROR:
                    $prefixesOfAllLogTypes[Log::ERROR] = 'Error: ';
                    $this->_addTrace(
                            $text, 
                            Arr::get($message, 'trace'), 
                            Arr::get($message, 'file'), 
                            Arr::get($message, 'line'), 
                            Arr::get($message, 'class'), 
                            Arr::get($message, 'function'), 
                            $additional
                    );
                    break;
                case Log::CRITICAL:
                    $prefixesOfAllLogTypes[Log::CRITICAL] = 'Critical: ';
                    $this->_addTrace(
                            $text, 
                            Arr::get($message, 'trace'), 
                            Arr::get($message, 'file'), 
                            Arr::get($message, 'line'), 
                            Arr::get($message, 'class'), 
                            Arr::get($message, 'function'), 
                            $additional
                    );
                    break;
                case Log::ALERT:
                    $prefixesOfAllLogTypes[Log::ALERT] = 'Alert: ';
                    $this->_addTrace(
                            $text, 
                            Arr::get($message, 'trace'), 
                            Arr::get($message, 'file'), 
                            Arr::get($message, 'line'), 
                            Arr::get($message, 'class'), 
                            Arr::get($message, 'function'), 
                            $additional
                    );
                    break;
                case Log::EMERGENCY:
                    $prefixesOfAllLogTypes[Log::EMERGENCY] = 'Emergency: ';
                    $this->_addTrace(
                            $text, 
                            Arr::get($message, 'trace'), 
                            Arr::get($message, 'file'), 
                            Arr::get($message, 'line'), 
                            Arr::get($message, 'class'), 
                            Arr::get($message, 'function'), 
                            $additional
                    );
                    break;
                case Log::NOTICE:
                case Log::INFO:
                default:
                    break;
            }
        }
        
        /*
         * if is set one from error type log, names will be added to eimail subject.
         * 
         * if table is empty, log contains only debug informations
         */
        if(count($prefixesOfAllLogTypes))
        {
            $iMostImportantErrorType = min(array_keys($prefixesOfAllLogTypes));

            $sLessImportantErrorType = Arr::get($prefixesOfAllLogTypes, $iMostImportantErrorType, '');

            $this->_title =  $sLessImportantErrorType . $this->_title;
        }
        
        if (count($messages))
        {
            $Email = Email::instance($this->_group, $this->_config)
                    ->from($config['from'])
                    ->subject($this->_title)
                    ->message(implode("<br />", $text), true);

            foreach ($config['to'] as $email)
            {
                $Email->to($email);
            }
            $Email->send();
        }
    }
    
    /**
     * replace e-mail subject
     * 
     * @param string $sNewTitle new e-mail subject
     * @return \Log_Email
     */
    public function setNewTitle($sNewTitle)
    {
        $this->_title = $sNewTitle;
        
        return $this;
    }
    
    /**
     * get current e-mail subject
     * 
     * @return string current e-mail subject
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    /**
     * add trace to raport message
     * 
     * @param array $text log message (extended in method)
     * @param array $aTrace trace to parse
     * @param string $sFile file name where log were added
     * @param int $iLine log where log were added
     * @param string $sClassName class name where log were added
     * @param string $sFunctionName function name where log were added
     * @param mixed $mAsdditional
     * @return \Log_Email
     */
    protected function _addTrace(&$text, $aTrace, $sFile, $iLine, $sClassName, $sFunctionName, $mAsdditional)
    {
        $text[] = 'Trace: <pre>' . print_r($aTrace, TRUE) . '</pre>';
        $text[] = 'File: ' . $sFile;
        $text[] = 'Line: ' . $iLine;
        $text[] = 'Class: ' . $sClassName;
        $text[] = 'Function: ' . $sFunctionName;
        $text[] = empty($mAsdditional) ? NULL : '<pre>' . print_r($mAsdditional, TRUE) . '</pre>';
        $text[] = '===============================';
        
        return $this;
    }
}
