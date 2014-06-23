<?php
namespace MoreHealth;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, $alias, array $args){
        switch($command->getName()){
            case "morehealth":
                if(count($args) < 1 || count($args) > 3){
                    $sender->sendMessage(TextFormat::RED . $command->getUsage());
                }else{
                    switch(count($args)){
                        case 1:
                            if($args[0] == "set"){
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Please specify an amount and a player: /morehealth set <amount> <player>");
                                }else{
                                    $sender->sendMessage(TextFormat::RED . "Please specify an amount: /morehealth set <amount>");
                                }
                            }elseif($args[0] == "restore"){
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Please specify a player: /morehealth restore <player>");
                                }else{
                                    $this->setMaxHealth($sender, 20, true);
                                    $sender->sendMessage(TextFormat::AQUA . "Your health has been restored to [20] points");
                                }
                            }else{
                                $sender->sendMessage(TextFormat::RED . $command->getUsage());
                            }
                            return true;
                            break;
                        case 2:
                            if($args[0] == "set"){
                                $amount = $args[1];
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Please specify a player: /morehealth set <amount> <player>");
                                }else{
                                    if(!is_numeric($amount)){
                                        $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                                    }else{
                                        $this->setMaxHealth($sender, $amount, true);
                                        $sender->sendMessage(TextFormat::AQUA . "You now have [$amount] points of health");
                                    }
                                }
                            }elseif($args[0] == "restore"){
                                $player = $this->getPlayer($args[1]);
                                if($player == false){
                                    $sender->sendMessage(TextFormat::RED . "[Error] Can't find player $args[1]");
                                }else{
                                    $this->setMaxHealth($player, 20, true);
                                    $player->sendMessage(TextFormat::AQUA . "Your health has been restored to [20] points");
                                    if($player != $sender){
                                        if(substr($args[0], -1, 1) == "s"){
                                            $sender->sendMessage(TextFormat::AQUA . "$args[1]' health has been restored to [20] points");
                                        }else{
                                            $sender->sendMessage(TextFormat::AQUA . "$args[1]'s health has been restored to [20] points");
                                        }
                                    }
                                }
                            }else{
                                $sender->sendMessage(TextFormat::RED . $command->getUsage());
                            }
                            return true;
                            break;
                        case 3:
                            if($args[0] == "set"){
                                $amount = $args[1];
                                $player = $this->getPlayer($args[2]);
                                if(!$sender->hasPermission("morehealth.set.other")){
                                    $sender->sendMessage(TextFormat::RED . $command->getPermissionMessage());
                                }else{
                                    if($player == false){
                                        $sender->sendMessage(TextFormat::RED . "[Error] Can't find player $args[2]");
                                    }else{
                                        if(!is_numeric($amount)){
                                            $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                                        }else{
                                            $this->setMaxHealth($player, $amount, true);
                                            $player->sendMessage(TextFormat::AQUA . "You now have [$amount] points of health");
                                            if($player != $sender){
                                                $sender->sendMessage(TextFormat::AQUA . "$args[2] now have [$amount] points of health");
                                            }
                                        }
                                    }
                                }
                            }elseif($args[0] == "restore"){
                                $sender->sendMessage(TextFormat::RED . "Usage: /morehealth restore <player>");
                            }else{
                                $sender->sendMessage(TextFormat::RED . $command->getUsage());
                            }
                            return true;
                            break;
                    }
                }
                return true;
                break;
        }
        return true;
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $this->setMaxHealth($player, $this->getMaxHealth($player));
    }

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

    public function setMaxHealth(Player $player, $amount, $save = false){
        if(!is_numeric($amount)){
            return false;
        }else{
            $player->setMaxHealth($amount);
            $player->setHealth($player->getMaxHealth());
            if($save == true){
                $this->saveMaxHealth($player, $amount);
            }
            return true;
        }
    }

    public function saveMaxHealth(Player $player, $amount){
        $config = new Config("plugins/MoreHealth.yml", Config::YAML);
        if(!is_numeric($amount)){
            return false;
        }else{
            $config->set($player->getName(), $amount);
            $config->save();
            return true;
        }
    }

    public function getMaxHealth(Player $player){
        $config = new Config("plugins/MoreHealth.yml", Config::YAML);
        if(!$config->exists($player->getName())){
            return false;
        }elseif(is_numeric($config->get($player->getName()))){
            return $config->get($player->getName());
        }else{
            return false;
        }
    }
}