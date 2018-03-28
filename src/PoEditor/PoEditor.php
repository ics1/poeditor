<?php
namespace ics1\PoEditor;


class PoEditor
{
    public $filePath;
    private $items = [];

    /** @var int */
    protected $lineNo;

    /**
     * PoEditor constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function parse()
    {
        $handle = fopen($this->filePath, 'r');
        $currentBlock = new Item();

        while (!feof($handle)) {
            $line = fgets($handle);
            if (trim($line) == '') {
                if ($currentBlock) {
                    $this->addItem($currentBlock);
                    $currentBlock = new Item();
                }
            } else {
                $currentBlock->process($line);
            }
        }

        fclose($handle);
        if ($currentBlock && $currentBlock->isInitialized()) {
            $this->addItem($currentBlock);
        }
    }

    /**
     * Return all parsed items
     * @return array|Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItem(Item $item)
    {
        $this->items[$item->getKey()] = $item;
    }

    /**
     * Fetch a item using a compiled key
     * @param $key
     * @return Item
     */
    public function getBlockWithKey($key)
    {
        if (isset($this->items[$key]))
            return $this->items[$key];
        return null;
    }

    /**
     * @param $msgid
     * @param null $context
     * @return \ics1\PoEditor\Item
     */
    public function getItem($msgid, $context = null)
    {
        if (is_array($msgid))
            $msgid = implode(" ", $msgid);
        $key = json_encode(['context' => $context, 'id' => $msgid]);
        return $this->getBlockWithKey($key);
    }

    /**
     * @return string
     */
    public function compile()
    {
        $result = [];
        foreach ($this->items as $key => $item)
            $result[] = $item->compile();
        return implode("\n\n", $result) . "\n";
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        $contextFile = $this->compile();
        $result = file_put_contents($this->filePath, $contextFile);
        if ($result === false) {
            throw new \Exception('Could not write into file '.$this->filePath);
        }
        return true;
    }

}