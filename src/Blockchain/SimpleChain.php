<?php
/**
 * Simple Blockchain Demo
 */
class Block {
    public $index;
    public $timestamp;
    public $data;
    public $prevHash;
    public $hash;
    
    public function __construct($index, $data, $prevHash = '') {
        $this->index = $index;
        $this->timestamp = time();
        $this->data = $data;
        $this->prevHash = $prevHash;
        $this->hash = $this->calculateHash();
    }
    
    public function calculateHash() {
        return hash('sha256', $this->index . $this->timestamp . json_encode($this->data) . $this->prevHash);
    }
}

class Blockchain {
    public $chain;
    
    public function __construct() {
        $this->chain = [$this->createGenesis()];
    }
    
    private function createGenesis() {
        return new Block(0, "Genesis Block", "0");
    }
    
    public function addBlock($data) {
        $prev = $this->chain[count($this->chain)-1];
        $block = new Block(count($this->chain), $data, $prev->hash);
        $this->chain[] = $block;
        return $block;
    }
}
?>
