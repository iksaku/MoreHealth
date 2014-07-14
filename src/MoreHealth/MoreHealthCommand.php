<?php
namespace MoreHealth;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MoreHealthCommand extends Command implements PluginIdentifiableCommand{
    public $plugin;

    public function __construct(Loader $plugin){
        parent::__construct("morehealth", "Change the player max health", "/morehealth <set|restore>", ["moreh", "mhealth", "mh"]);
        $this->setPermission("morehealth");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, $alias, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }
        if(count($args) < 1 || count($args) > 3){
            $sender->sendMessage(TextFormat::RED . $this->getUsage());
        }else{
            if(!$sender->hasPermission("morehealth.set") || !$sender->hasPermission("morehealth.restore")){
                $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
            }else{
                switch(count($args)){
                    case 1:
                        switch($args[0]){
                            case "setdefault":
                                $sender->sendMessage(TextFormat::RED . "Please specify an amount: /morehealth setdefault <amount>");
                                return true;
                                break;
                            case "set":
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Please specify an amount and a player: /morehealth set <amount> <player>");
                                }else{
                                    $sender->sendMessage(TextFormat::RED . "Please specify an amount: /morehealth set <amount>");
                                }
                                return true;
                                break;
                            case "restore":
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Please specify a player: /morehealth restore <player>");
                                }else{
                                    $this->plugin->setMaxHealth($sender, 20, true);
                                    $sender->sendMessage(TextFormat::AQUA . "Your health has been restored to [20] points");
                                }
                                return true;
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . $this->getUsage());
                                break;
                        }
                        return true;
                        break;
                    case 2:
                        switch($args[0]){
                            case "setdefault":
                                $amount = $args[1];
                                if(!is_numeric($amount)){
                                    $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                                }else{
                                    $this->plugin->setDefaultHealth($amount);
                                    $sender->sendMessage(TextFormat::AQUA . "Successfully changed the default health limit.");
                                }
                                return true;
                                break;
                            case "set":
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Please specify a player: /morehealth set <amount> <player>");
                                }else{
                                    $amount = $args[1];
                                    if(!is_numeric($amount)){
                                        $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                                    }else{
                                        $this->plugin->setMaxHealth($sender, $amount, true);
                                        $sender->sendMessage(TextFormat::AQUA . "You now have [$amount] points of health");
                                    }
                                }
                                return true;
                                break;
                            case "restore":
                                if(!$sender->hasPermission("morehealth.restore.other")){
                                    $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                                }else{
                                    $player = $this->plugin->getPlayer($args[1]);
                                    if($player == false){
                                        $sender->sendMessage(TextFormat::RED . "[Error] Can't find player $args[1]");
                                    }else{
                                        $this->plugin->setMaxHealth($player, 20, true);
                                        $player->sendMessage(TextFormat::AQUA . "Your health has been restored to [20] points");
                                        if($player != $sender){
                                            if(substr($args[0], -1, 1) == "s"){
                                                $sender->sendMessage(TextFormat::AQUA . "$args[1]' health has been restored to [20] points");
                                            }else{
                                                $sender->sendMessage(TextFormat::AQUA . "$args[1]'s health has been restored to [20] points");
                                            }
                                        }
                                    }
                                }
                                return true;
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . $this->getUsage());
                                break;
                        }
                        return true;
                        break;
                    case 3:
                        switch($args[0]){
                            case "setdefault":
                                $sender->sendMessage(TextFormat::RED . "Usage: /morehealth setdefault <amount>");
                                return true;
                            case "set":
                                if(!$sender->hasPermission("morehealth.set.other")){
                                    $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                                }else{
                                    $amount = $args[1];
                                    $player = $this->plugin->getPlayer($args[2]);
                                    if($player == false){
                                        $sender->sendMessage(TextFormat::RED . "[Error] Can't find player $args[2]");
                                    }else{
                                        if(!is_numeric($amount)){
                                            $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                                        }else{
                                            $this->plugin->setMaxHealth($player, $amount, true);
                                            $player->sendMessage(TextFormat::AQUA . "You now have [$amount] points of health");
                                            if($player != $sender){
                                                $sender->sendMessage(TextFormat::AQUA . "$args[2] now have [$amount] points of health");
                                            }
                                        }
                                    }
                                }
                                return true;
                                break;
                            case "restore":
                                $sender->sendMessage(TextFormat::RED . "Usage: /morehealth restore <player>");
                                return true;
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . $this->getUsage());
                                break;
                        }
                        return true;
                        break;
                }
            }
        }
        return true;
    }

    public function getPlugin(){
        return $this->plugin;
    }
} 