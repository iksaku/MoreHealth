<?php
namespace MoreHealth;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;

class EventHandler implements Listener{
    /** @var Loader  */
    public $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $this->plugin->setPlayerMaxHealth($player, $this->plugin->getPlayerMaxHealth($player));
    }
} 