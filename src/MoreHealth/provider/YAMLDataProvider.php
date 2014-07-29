<?php
namespace MoreHealth\provider;

use MoreHealth\Loader;
use pocketmine\Player;
use pocketmine\utils\Config;

class YAMLDataProvider implements DataProvider{
    /** @var Loader  */
    protected $plugin;

    /** @var Config  */
    protected $database;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        $this->database = new Config($plugin->getDataFolder() . "healths.yml", Config::YAML);
    }

    public function getPlayerMaxHealth(Player $player){
        if(!$this->database->exists($player->getName())){
            return $this->plugin->getDefaultHealth();
        }
        return $this->database->get($player->getName());
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
        if(!$this->database->exists($player->getName())){
            return false;
        }
        $this->database->remove(strtolower($player->getName()));
        $this->database->save();
        return true;
    }

    public function savePlayerMaxHealth(Player $player, $amount){
        if(!is_numeric($amount)){
            return false;
        }
        $this->database->set(strtolower($player->getName()), $amount);
        $this->database->save();
        return true;
    }

    public function close(){}
} 