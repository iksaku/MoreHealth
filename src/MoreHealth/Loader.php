<?php
namespace MoreHealth;

use MoreHealth\provider\DataProvider;
use MoreHealth\provider\SQLite3DataProvider;
use MoreHealth\provider\YAMLDataProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase implements Listener{
    /** @var  Config */
    protected $database;

    public function onEnable(){
        @mkdir($this->getDataFolder());

        $this->database = new Config($this->getDataFolder() . "Health.yml", Config::YAML);
        if(!$this->database->exists("defaulthealth") || !is_numeric($this->database->get("defaulthealth"))){
            $this->database->set("defaulthealth", 20);
            $this->database->save();
        }

        $this->getServer()->getCommandMap()->register("morehealth", new MoreHealthCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);

        foreach($this->getServer()->getOnlinePlayers() as $p){
            $p->setMaxHealth($this->getPlayerMaxHealth($p));
        }
    }

    public function onDisable(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $p->setMaxHealth(20);
        }
    }

    /*
     *  .----------------.  .----------------.  .----------------.
     * | .--------------. || .--------------. || .--------------. |
     * | |      __      | || |   ______     | || |     _____    | |
     * | |     /  \     | || |  |_   __ \   | || |    |_   _|   | |
     * | |    / /\ \    | || |    | |__) |  | || |      | |     | |
     * | |   / ____ \   | || |    |  ___/   | || |      | |     | |
     * | | _/ /    \ \_ | || |   _| |_      | || |     _| |_    | |
     * | ||____|  |____|| || |  |_____|     | || |    |_____|   | |
     * | |              | || |              | || |              | |
     * | '--------------' || '--------------' || '--------------' |
     *  '----------------'  '----------------'  '----------------'
     *
     */

    /**
     * Let you search for a player using his Display name(Nick) or Real name
     *
     * @param $player
     * @return bool|Player
     */
    public function getPlayer($player){
        $player = strtolower($player);
        $r = false;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if(strtolower($p->getDisplayName()) === $player || strtolower($p->getName()) === $player){
                $r = $p;
            }
        }
        return $r;
    }

    /**
     * Return the default health specified in the config
     *
     * @return bool|int
     */
    public function getDefaultHealth(){
        if(!$this->database->exists("defaulthealth") || !is_numeric($this->database->get("defaulthealth"))){
            $this->database->set("defaulthealth", 20);
            $this->database->save();
        }
        return $this->database->get("defaulthealth");
    }

    /**
     * Modify the default health for players (saved in config)
     *
     * @param int $amount
     * @return bool
     */
    public function setDefaultHealth($amount){
        if(!is_numeric($amount)){
            return false;
        }
        $this->database->set("defaulthealth", $amount);
        $this->database->save();
        return true;
    }

    /**
     * Return the Max health of a player if modified,
     * else it will return the default health limit
     *
     * @param Player $player
     * @return int
     */
    public function getPlayerMaxHealth(Player $player){
        if(!$this->database->exists($player->getName())){
            return $this->getDefaultHealth();
        }
        return $this->database->get($player->getName());
    }

    /**
     * Modify player's health limit
     *
     * @param Player $player
     * @param int $amount
     * @param bool $save
     * @return bool
     */
    public function setPlayerMaxHealth(Player $player, $amount, $save = false){
        if(!is_numeric($amount)){
            return false;
        }
        $player->setMaxHealth($amount);
        $player->heal($amount);
        if($save === true){
            $this->savePlayerMaxHealth($player, $amount);
        }
        return true;
    }

    /**
     * Saves the health options
     *
     * @param Player $player
     * @param int $amount
     */
    private function savePlayerMaxHealth(Player $player, $amount){
        $this->database->set($player->getName(), $amount);
        if($amount === $this->getDefaultHealth()){
            $this->database->remove($player->getName());
        }
        $this->database->save();
    }
}