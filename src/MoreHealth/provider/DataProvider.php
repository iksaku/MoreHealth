<?php
namespace MoreHealth\provider;


use MoreHealth\Loader;
use pocketmine\Player;

interface DataProvider {
    /**
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin);

    /**
     * @param Player $player
     * @return mixed
     */
    public function getPlayerMaxHealth(Player $player);

    /**
     * @param Player $player
     * @param int $amount
     * @param bool $save
     * @return bool
     */
    public function setPlayerMaxHealth(Player $player, $amount, $save = false);

    /**
     * @param Player $player
     * @return bool
     */
    public function restorePlayerMaxHealth(Player $player);

    /**
     * @param Player $player
     * @param int $amount
     * @return bool
     */
    public function savePlayerMaxHealth(Player $player, $amount);

    public function close();
} 