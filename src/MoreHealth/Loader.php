<?php
namespace MoreHealth;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase implements Listener{

    public function onEnable(){
        @mkdir("plugins/MoreHealth/");
        $this->getHealthConfig();
        $this->getDefaultHealth();
        $this->getServer()->getCommandMap()->register("morehealth", new MoreHealthCommand($this));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable(){
        $this->getConfig()->save();
        $this->getHealthConfig()->save();
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $this->setMaxHealth($player, $this->getMaxHealth($player));
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

    public function getPlayer($name){
        $r = "";
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if($p->getName() == $name || $p->getDisplayName() == $name){
                $r = $this->getServer()->getPlayerExact($p->getName());
            }
        }
        if($r == ""){
            return false;
        }else{
            return $r;
        }
    }

    public function getHealthConfig(){
        return new Config("plugins/MoreHealth/MoreHealth.yml", Config::YAML);
    }

    public function getDefaultHealth(){
        if(!file_exists("plugins/MoreHealth/config.yml")){
            $this->saveDefaultConfig();
        }
        if(!is_numeric($this->getConfig()->get("defaulthealth"))){
            $this->getLogger()->error(TextFormat::RED . "[MoreHealth] Invalid value for \"defaulthealth\" in plugins/MoreHealth/config.yml");
            return false;
        }
        return $this->getConfig()->get("defaulthealth");
    }

    public function getMaxHealth(Player $player){
        if($this->getHealthConfig()->exists($player->getName())){
            return $this->getHealthConfig()->get($player->getName());
        }
        return $this->getDefaultHealth();
    }

    public function setDefaultHealth($amount){
        $this->getHealthConfig()->set("defaulthealth", $amount);
    }

    public function setMaxHealth(Player $player, $amount, $save = false){
        if(!is_numeric($amount)){
            return false;
        }else{
            $player->setMaxHealth($amount);
            $player->setHealth($player->getMaxHealth());
            if($save == true){
                if($amount == $this->getDefaultHealth()){
                    $this->removeMaxHealth($player);
                }else{
                    $this->saveMaxHealth($player, $amount);
                }
            }
            return true;
        }
    }

    public function removeMaxHealth(Player $player){
        if(!$this->getHealthConfig()->exists($player->getName())){
            return false;
        }else{
            $this->getHealthConfig()->remove($player->getName());
            $this->getHealthConfig()->save();
            return true;
        }
    }

    public function saveMaxHealth(Player $player, $amount){
        if(!is_numeric($amount)){
            return false;
        }else{
            $this->getHealthConfig()->set($player->getName(), $amount);
            $this->getHealthConfig()->save();
            return true;
        }
    }
}