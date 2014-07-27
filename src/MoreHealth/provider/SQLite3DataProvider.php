<?php
namespace MoreHealth\provider;

use MoreHealth\Loader;
use pocketmine\Player;

class SQLite3DataProvider implements DataProvider{
    /** @var Loader  */
    protected $plugin;

    /** @var \SQLite3  */
    protected $db;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        if(!file_exists($plugin->getDataFolder() . "healths.db")){
            $this->db = new \SQLite3($plugin->getDataFolder() . "healths.db", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            $resources = $plugin->getResource("sqlite3.sql");
            $this->db->exec(stream_get_contents($resources));
        }else{
            $this->db = new \SQLite3($plugin->getDataFolder() . "healths.db", SQLITE3_OPEN_READWRITE);
        }
    }

    public function getPlayerMaxHealth(Player $player){
        $name = trim(strtolower($player->getName()));
        $prepare = $this->db->prepare("SELECT * FROM players WHERE name = :name");
        $prepare->bindValue(":name", $name, SQLITE3_TEXT);
        $r = $prepare->execute();

        if($r instanceof \SQLite3Result){
            $health = $r->fetchArray(SQLITE3_ASSOC);
            $r->finalize();
            if(isset($health["name"]) && $health["name"] == $name){
                unset($health["name"]);
                $prepare->close();
                return $health;
            }
        }
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
        $prepare = $this->db->prepare("DELETE FROM players WHERE name = :name");
        $prepare->bindValue(":name", $name, SQLITE3_TEXT);
        $prepare->execute();
    }

    public function savePlayerMaxHealth(Player $player, $amount){
        $name = trim(strtolower($player->getName()));
        $prepare = $this->db->prepare("INSERT INTO players (name, health) VALUES (:name, :health)");
        $prepare->bindValue(":name", $name, SQLITE3_TEXT);
        $prepare->bindValue(":health", $amount, SQL_NUMERIC);
        $prepare->execute();
    }
} 