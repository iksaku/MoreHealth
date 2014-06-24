<?php
namespace MoreHealth;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
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
				if(!isset($args[0])){
					return false;
				}
				switch($args[0]){
					case "set":
						if(!$sender->hasPermission("morehealth.set")){
							$sender->sendMessage($command->getPermissionMessage());
							return true;
						}
						if(!isset($args[1])){
							$sender->sendMessage(TextFormat::RED."Usage: /morehealth set <hearts> [player]");
							return true;
						}
						if(($sender instanceof ConsoleCommandSender) and !isset($args[2])){ // RemoteConsoleXxx instanceof ConsoleXxx
							$sender->sendMessage(TextFormat::RED."Usage: /morehealth set <hearts> <player>");
						}
						$hearts = $args[1];
						$amount = (int) ($hearts * 2);
						/** @var Player $target */
						$target = $sender;
						if(isset($args[2])){
							$target = $this->getServer()->getPlayer($args[2]);
							if(!($target instanceof Player)){
								$sender->sendMessage(TextFormat::YELLOW."No players with a name similar to $args[2] are found.");
								return true;
							}
						}
						$this->setMaxHealth($target, $amount, true);
						return true;
					case "restore":
						if(!isset($args[1]) and ($sender instanceof ConsoleCommandSender)){
							$sender->sendMessage(TextFormat::RED."Usage: /morehealth restore <player>");
							return true;
						}
						/** @var Player $target */
						$target = $sender;
						if(isset($args[1])){
							$target = $this->getServer()->getPlayer($args[1]); // this function gets the player object with the name most similar to $args[1]
							if(!($target instanceof Player)){
								$sender->sendMessage(TextFormat::YELLOW."No players with a name similar to $args[1] are found.");
								return true;
							}
						}
						$this->setMaxHealth($target, 20, true);
						return true;
					default:
						return false;
				}
			default:
				return false;
		}
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
//				$this->saveMaxHealth($player, $amount);
				// I think PocketMine will store it later. Anyway, if you I/O the config every time, it will lag large servers, especially <quote>minigame servers</quote>.
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
			return 20;
		}elseif(is_numeric($config->get($player->getName()))){
			return $config->get($player->getName());
		}else{
			return false;
		}
	}
}