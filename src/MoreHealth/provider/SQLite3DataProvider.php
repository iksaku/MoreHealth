<?php
namespace MoreHealth\provider;

use MoreHealth\Loader;
use pocketmine\Player;

class SQLite3DataProvider implements DataProvider{
    /** @var \MoreHealth\Loader  */
    protected $plugin;

    /** @var \SQLite3  */
    protected $database;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        if(!file_exists($this->plugin->getDataFolder() . "healths.db")){
            $this->database = new \SQLite3($this->plugin->getDataFolder() . "healths.db", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE); //Only work with ":memory:" as path? :/
            $resources = $this->plugin->getResource("sqlite3.sql");
            $this->database->exec(stream_get_contents($resources));
        }else{
            $this->database = new \SQLite3($this->plugin->getDataFolder() . "healths.db", SQLITE3_OPEN_READWRITE);
        }
    }

    public function getPlayerMaxHealth(Player $player){
        $name = trim(strtolower($player->getName()));
        $prepare = $this->database->prepare("SELECT * FROM players WHERE name = :name");
        $prepare->bindValue(":name", $name, SQLITE3_TEXT);
        $r = $prepare->execute();

        //If player exists in the DB:
        if($r instanceof \SQLite3Result){
            $health = $r->fetchArray(SQLITE3_ASSOC);
            $r->finalize();
            if(isset($health["name"]) && $health["name"] == $name){
                unset($health["name"]);
                $prepare->close();
                return $health;
            }
        }

        //If player doesn't exists in the DB:
        $prepare->close();
        return $this->plugin->getDefaultHealth();
    }

    public function setPlayerMaxHealth(Player $player, $amount, $save = false){
        if(!is_numeric($amount)){
            return false;
        }
        $player->setMaxHealth($amount);
        $player->heal($amount);
        if($save === true){
            if($amount == $this->plugin->getDefaultHealth()){
                $this->restorePlayerMaxHealth($player);
            }else{
                $this->savePlayerMaxHealth($player, $amount);
            }
        }
        return true;
    }

    public function restorePlayerMaxHealth(Player $player){
        $name = trim(strtolower($player->getName()));
        if($this->getPlayerMaxHealth($player) !== $this->plugin->getDefaultHealth()){
            $prepare = $this->database->prepare("DELETE FROM players WHERE name = :name");
            $prepare->bindValue(":name", $name, SQLITE3_TEXT);
            $prepare->execute();
        }
        return true;
    }

    public function savePlayerMaxHealth(Player $player, $amount){
        $name = trim(strtolower($player->getName()));
        if($this->getPlayerMaxHealth($player) !== $this->plugin->getDefaultHealth()){
            $prepare = $this->database->prepare("UPDATE players SET health = :health WHERE name = :name");
            $prepare->bindValue(":name", $name, SQLITE3_TEXT);
            $prepare->bindValue(":health", $amount, SQLITE3_INTEGER);
            $prepare->execute();
        }
        return true;
    }

    public function close(){
        $this->database->close();
    }
} 