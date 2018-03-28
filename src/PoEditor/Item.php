<?php
namespace ics1\PoEditor;
/**
 * Class Item
 * @package ics1\PoEditor
 */
class Item
{
    const NEWLINE = "\n";
    /**
     * @var array
     */
    public $msgid = [];

    /**
     * @var array
     */
    public $msgstr = [];

    /**
     * @var
     */
    public $msgctxt;

    /**
     * @var array
     */
    public $comments = [];

    /**
     * @var bool
     */
    private $lastProcessed;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @param $string
     */
    public function process($string)
    {
        $string = trim($string);
        if ($string[0] == '"') {
            if ($this->lastProcessed)
                $this->processLine($this->lastProcessed, $string);
        } else if ($string[0] == '#') {
            $this->comments[] = $string;
        } else {
            list($car, $cdr) = explode(' ', $string, 2);
            $this->processLine($car, $cdr);
        }
    }

    /**
     * @param $car
     * @param $cdr
     */
    public function processLine($car, $cdr)
    {
        $clean_string = preg_replace('/^("(.*)")$/', '$2$3', $cdr);
        switch ($car) {
            case 'msgctxt':
                $this->msgctxt = $clean_string;
                break;
            case 'msgid':
            case 'msgstr':
                array_push($this->$car, $clean_string);
                break;
        }

        $this->lastProcessed = $car;
    }

    /**
     * @return string
     */
    public function compile()
    {
        // can happen if it parses only comments/artifacts
        if (!count($this->msgid))
            return "";
        $str = "";
        if ($this->comments)
            $str .= implode(self::NEWLINE, $this->comments) . self::NEWLINE;

        $includedItems = ['msgid'];
        $includedItems[] = 'msgstr';
        foreach ($includedItems as $key) {
            if (is_array($this->$key)) {
                $str .= "$key ";
                $str .= implode(self::NEWLINE, array_map([$this, 'quoteWrap'], $this->$key)) . self::NEWLINE;
            }
        }

        return trim($str);
    }

    /**
     * @param $str
     * @return string
     */
    private function quoteWrap($str)
    {
        return '"' . $str . '"';
    }

    /**
     * @return array
     */
    public function getMsgId()
    {
        return $this->msgid;
    }

    /**
     * @param array $msgId
     */
    public function setMsgId($msgId)
    {
        if (!is_array($msgId))
            $msgId = [$msgId];
        $this->msgid = $msgId;
    }

    /**
     * @return array
     */
    public function getMsgStr()
    {
        return $this->msgstr;
    }

    /**
     * @param array $msgStr
     */
    public function setMsgStr($msgStr)
    {
        if (!is_array($msgStr))
            $msgStr = [$msgStr];
        $this->msgstr = $msgStr;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param array $comment
     */
    public function setComments($comment)
    {
        if (!is_array($comment))
            $comment = [$comment];
        $this->comments = $comment;
    }

    /**
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * @param boolean $initialized
     */
    public function setInitialized($initialized)
    {
        $this->initialized = $initialized;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return json_encode(['context' => $this->msgctxt, 'id' => implode("", $this->getMsgId())]);
    }
}