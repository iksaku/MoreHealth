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
        parent::__construct("morehealth", "Change the player max health", "/morehealth <setdefault|restoredefault|set|restore>", ["moreh", "mhealth", "mh"]);
        $this->setPermission("morehealth");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, $alias, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }
        if(!$sender->hasPermission("morehealth.setdefault") || !$sender->hasPermission("morehealth.set") || !$sender->hasPermission("morehealth.restore")){
            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
            return false;
        }
        switch(count($args)){
            case 1:
                switch(strtolower($args[0])){
                    case "setdefault":
                        if(!$sender->hasPermission("morehealth.setdefault")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . "Please specify an amount: /morehealth setdefault <amount>");
                        break;
                    case "set":
                        if(!$sender->hasPermission("morehealth.set.use")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "Please specify an amount: /morehealth set <amount>" : "Please specify an amount and a player: /morehealth set <amount> <player>"));
                        break;
                    case "restoredefault":
                        if(!$sender->hasPermission("morehealth.restoredefault")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $this->plugin->setDefaultHealth(20);
                        $sender->sendMessage(TextFormat::AQUA . "Successfully restored the default health limit to 20");
                        break;
                    case "restore":
                        if(!$sender->hasPermission("morehealth.restore.use")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Please specify a player: /morehealth restore <player>");
                            return false;
                        }
                        $this->plugin->setPlayerMaxHealth($sender, $this->plugin->getDefaultHealth(), true);
                        $sender->sendMessage(TextFormat::AQUA . "Your health has been restored to [" . $this->plugin->getDefaultHealth() . "] points");
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . $this->getUsage());
                        break;
                }
                break;
            case 2:
                switch(strtolower($args[0])){
                    case "setdefault":
                        if(!$sender->hasPermission("morehealth.setdefault")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $amount = $args[1];
                        if(!is_numeric($amount)){
                            $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                            return false;
                        }
                        $this->plugin->setDefaultHealth($amount);
                        $sender->sendMessage(TextFormat::AQUA . "Successfully changed the default health limit to " . $amount);
                        break;
                    case "set":
                        if(!$sender->hasPermission("morehealth.set")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Please specify a player: /morehealth set <amount> <player>");
                            return false;
                        }
                        $amount = $args[1];
                        if(!is_numeric($amount)){
                            $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                            return false;
                        }
                        $this->plugin->setPlayerMaxHealth($sender, $amount, true);
                        $sender->sendMessage(TextFormat::AQUA . "You now have [" . $amount . "] points of health");
                        break;
                    case "restoredefault":
                        if(!$sender->hasPermission("morehealth.restoredefault")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/restoredefault");
                        break;
                    case "restore":
                        if(!$sender->hasPermission("morehealth.restore.use")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        if(!$sender->hasPermission("morehealth.restore.other")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $player = $this->plugin->getPlayer($args[1]);
                        if(!$player){
                            $sender->sendMessage(TextFormat::RED . "[Error] Can't find player " . $args[1]);
                            return false;
                        }
                        $this->plugin->setPlayerMaxHealth($player, $this->plugin->getDefaultHealth(), true);
                        $player->sendMessage(TextFormat::AQUA . "Your health has been restored to [" . $this->plugin->getDefaultHealth() . "] points");
                        if($player->getName() !== $sender->getName()){
                            $sender->sendMessage(TextFormat::AQUA . $args[1] . (substr($args[1], -1, 1) === "s" ? "'" : "'s") . " health has been restored to [" . $this->plugin->getDefaultHealth() . "] points");
                        }
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . $this->getUsage());
                        break;
                }
                break;
            case 3:
                switch(strtolower($args[0])){
                    case "setdefault":
                        if(!$sender->hasPermission("morehealth.setdefault")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/morehealth setdefault <amount>");
                        break;
                    case "set":
                        if(!$sender->hasPermission("morehealth.set.other")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                        }
                        $amount = $args[1];
                        $player = $this->plugin->getPlayer($args[2]);
                        if($player == false){
                            $sender->sendMessage(TextFormat::RED . "[Error] Can't find player " . $args[2]);
                            return false;
                        }
                        if(!is_numeric($amount)){
                            $sender->sendMessage(TextFormat::YELLOW . "Invalid health amount, please use numbers");
                            return false;
                        }
                        $this->plugin->setPlayerMaxHealth($player, $amount, true);
                        $player->sendMessage(TextFormat::AQUA . "You now have [$amount] points of health");
                        if($player->getName() !== $sender->getName()){
                            $sender->sendMessage(TextFormat::AQUA . $args[2] . " now have [" . $amount . "] points of health");
                        }
                        break;
                    case "restoredefault":
                        if(!$sender->hasPermission("morehealth.restoredefault")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/restoredefault");
                        break;
                    case "restore":
                        if(!$sender->hasPermission("morehealth.restore.other")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . "Usage: /morehealth restore <player>");
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . $this->getUsage());
                        break;
                }
                break;
            default:
                $sender->sendMessage(TextFormat::RED . $this->getUsage());
                return false;
                break;
        }
        return true;
    }

    public function getPlugin(){
        return $this->plugin;
    }
} 