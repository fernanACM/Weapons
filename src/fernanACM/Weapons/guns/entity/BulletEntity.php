<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons\guns\entity;

use pocketmine\player\Player;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;

use pocketmine\world\Explosion;

use fernanACM\Weapons\guns\GunData;

class BulletEntity extends ItemEntity{

    /** @var string $gunType */
    public string $gunType;

    /** @var Entity $exempt */
    public Entity $exempt;

    public function __construct(Location $location, Item $item, ?CompoundTag $nbt){
        parent::__construct($location, $item, $nbt);
    }

    /**
     * @param integer $currentTick
     * @return boolean
     */
    public function onUpdate(int $currentTick): bool{
        if($this->onGround){
                $this->flagForDespawn();

                if(isset(GunData::EXPLODE[$this->gunType])){
                        $rad = GunData::EXPLODE[$this->gunType];

                        $explode = new Explosion($this->getPosition(), $rad);
                        $explode->explodeB();
                }
        }
        return parent::onUpdate($currentTick);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function onCollideWithPlayer(Player $player): void{
        if(!$this->onGround){
            if($player === $this->exempt)return;
                $event = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_ENTITY_ATTACK, GunData::DAMAGES[$this->gunType]);
                $event->setAttackCooldown(0);
                $player->attack($event);
                if(isset(GunData::EXPLODE[$this->gunType])){
                        $rad = GunData::EXPLODE[$this->gunType];
                        $explode = new Explosion($this->getPosition(), $rad);
                        $explode->explodeB();
            }
            $this->flagForDespawn();
        }
    }
}