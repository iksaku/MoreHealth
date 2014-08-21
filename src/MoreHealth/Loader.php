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
    /** @var  DataProvider */
    protected $provider;

    /** @var  Config */
    public $health;

    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();

        $this->getServer()->getCommandMap()->register("morehealth", new MoreHealthCommand($this));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        foreach($this->getServer()->getOnlinePlayers() as $p){
            $p->setMaxHealth($this->getPlayerMaxHealth($p));
        }

        switch(strtolower($this->getConfig()->get("database"))){
            case "yaml":
                $this->provider = new YAMLDataProvider($this);
                break;
            case "sqlite3":
                $this->provider = new SQLite3DataProvider($this); //TODO
                break;
            default:
                $this->getLogger()->error(TextFormat::RED . "Unknown Database provided on \"plugins/MoreHealth/config.yml\", MoreHealth will be disabled");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                $this->setEnabled(false);
                break;
        }
    }

    public function onDisable(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $p->setMaxHealth(20);
        }
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $this->setPlayerMaxHealth($player, $this->getPlayerMaxHealth($player));
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
     * @return bool|mixed
     */
    public function getDefaultHealth(){
        if(!$this->getConfig()->exists("defaulthealth") || !is_numeric($this->getConfig()->get("defaulthealth"))){
            $this->getConfig()->set("defaulthealth", 20);
        }
        return $this->getConfig()->get("defaulthealth");
    }

    /**
     * Modify the default health for players (saved in config)
     *
     * @param $amount
     * @return bool
     */
    public function setDefaultHealth($amount){
        if(!is_numeric($amount)){
            return false;
        }
        $this->getConfig()->set("defaulthealth", $amount);
        $this->getConfig()->save();
        return true;
    }

    /**
     * Return the Max health of a player if modified,
     * else it will return the default health limit
     *
     * @param Player $player
     * @return mixed
     */
    public function getPlayerMaxHealth(Player $player){
        return $this->provider->getPlayerMaxHealth($player);
    }

    /**
     * Modify player's health limit
     *
     * @param Player $player
     * @param $amount
     * @param bool $save
     */
    public function setPlayerMaxHealth(Player $player, $amount, $save = false){
        $this->provider->setPlayerMaxHealth($player, $amount, $save);
    }

    /**
     * Restore player's health to the default in config
     *
     * @param Player $player
     */
    public function restorePlayerMaxHealth(Player $player){
        $this->provider->restorePlayerMaxHealth($player);
    }
}