<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons\guns;

use pocketmine\player\Player;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;

use fernanACM\Weapons\Loader;
use fernanACM\Weapons\guns\GunData;
use fernanACM\Weapons\guns\entity\BulletEntity;
use fernanACM\Weapons\task\FireGunTask;

class GunManager{

    /** @var FireGunTask[] $tasks */
    private static array $tasks = [];

    private const MG42 = "mg42";
    private const MP40 = "mp40";
    private const MINIGUN = "minigun";
    private const THOMPSON = "thompson";
    private const M1911 = "m1911";
    private const PANZERFAUST = "panzerfaust";

    /**
     * @param Player $player
     * @param string $gun
     * @param integer|null $amount
     * @return void
     */
    public static function giveGun(Player $player, string $gun, ?int $amount): void{
        switch($gun){
            case self::MG42:
                $typeName = str_replace(["{GUN}"], [self::MG42], Loader::getMessage($player, "Weapons.guns.type.MG42-name"));
                $item = VanillaItems::GOLDEN_HOE();
                $item->setCustomName($typeName);
                $item->setUnbreakable(true);
                $item->setCustomBlockData(CompoundTag::create()->setString("gunType", $gun));
                $item->setCount($amount ?? 1);
                $player->getInventory()->addItem($item);
            break;

            case self::MP40:
                $typeName = str_replace(["{GUN}"], [self::MP40], Loader::getMessage($player, "Weapons.guns.type.MP40-name"));
                $item = VanillaItems::STONE_AXE();
                $item->setCustomName($typeName);
                $item->setUnbreakable(true);
                $item->setCustomBlockData(CompoundTag::create()->setString("gunType", $gun));
                $item->setCount($amount ?? 1);
                $player->getInventory()->addItem($item);
            break;

            case self::MINIGUN:
                $typeName = str_replace(["{GUN}"], [self::MINIGUN], Loader::getMessage($player, "Weapons.guns.type.MINIGUN-name"));
                $item = VanillaItems::IRON_PICKAXE();
                $item->setCustomName($typeName);
                $item->setUnbreakable(true);
                $item->setCustomBlockData(CompoundTag::create()->setString("gunType", $gun));
                $item->setCount($amount ?? 1);
                $player->getInventory()->addItem($item);
            break;

            case self::THOMPSON:
                $typeName = str_replace(["{GUN}"], [self::THOMPSON], Loader::getMessage($player, "Weapons.guns.type.THOMPSON-name"));
                $item = VanillaItems::DIAMOND_HOE();
                $item->setCustomName($typeName);
                $item->setUnbreakable(true);
                $item->setCustomBlockData(CompoundTag::create()->setString("gunType", $gun));
                $item->setCount($amount ?? 1);
                $player->getInventory()->addItem($item);
            break;

            case self::M1911:
                $typeName = str_replace(["{GUN}"], [self::M1911], Loader::getMessage($player, "Weapons.guns.type.M1911-name"));
                $item = VanillaItems::WOODEN_SHOVEL();
                $item->setCustomName($typeName);
                $item->setUnbreakable(true);
                $item->setCustomBlockData(CompoundTag::create()->setString("gunType", $gun));
                $item->setCount($amount ?? 1);
                $player->getInventory()->addItem($item);
            break;

            case self::PANZERFAUST:
                $typeName = str_replace(["{GUN}"], [self::PANZERFAUST], Loader::getMessage($player, "Weapons.guns.type.PANZERFAUST-name"));
                $item = VanillaItems::IRON_SWORD();
                $item->setCustomName($typeName);
                $item->setUnbreakable(true);
                $item->setCustomBlockData(CompoundTag::create()->setString("gunType", $gun));
                $item->setCount($amount ?? 1);
                $player->getInventory()->addItem($item);
            break;
        }
    }

    /**
     * @param Player $player
     * @param integer $ammo
     * @return void
     */
    public static function giveAmmo(Player $player, int $amount): void{
        $item = VanillaItems::SLIMEBALL();
        $item->setCustomName(Loader::getMessage($player, "Weapons.ammo.AMMO-name"));
        $item->setCustomBlockData(CompoundTag::create()->setInt("ammoAmount", $amount));
        $player->getInventory()->addItem($item);
    }

    /**
     * @param Player $player
     * @param Item $gun
     * @param Item|null $ammo
     * @param boolean $tip
     * @return bool
     */
    public static function onFire(Player $player, Item $gun, Item $ammo = null, bool $tip = true): bool{
        if(is_null($ammo)){
            $slot = 0;
            foreach($player->getInventory()->getContents() as $i => $item){
                if($item->hasCustomBlockData() && $item->getCustomBlockData()->getTag("ammoAmount")){
                    $slot = $i;
                    $ammo = $item;
                    break;
                }
            }
            if(is_null($ammo)) return false;
            $amount = $ammo->getCustomBlockData()->getInt("ammoAmount");
            --$amount;
            if($amount <= 0){
                if($ammo->getCount() > 1){
                    $player->getInventory()->setItem($slot, $ammo->setCount($ammo->getCount() - 1));
                }else{
                    $ammo->setCustomBlockData(CompoundTag::create()->setInt("ammoAmount", $amount));
                    $player->getInventory()->setItem($slot, $ammo->setCount($ammo->getCount() - 1));
                }
            }else{
                $ammo->setCustomBlockData(CompoundTag::create()->setInt("ammoAmount", $amount));
                $player->getInventory()->setItem($slot, $ammo);
            }
            $tipAmount = str_replace(["{AMOUNT}"], [$amount], Loader::getMessage($player, "Weapons.ammo.ammo-tip"));
            if($tip && $amount >= 1) $player->sendTip($tipAmount);
        }
        $gunType = $gun->getCustomBlockData()->getString("gunType");
        $itemTag = $ammo->setCount(1)->nbtSerialize();
        $itemTag->setString("Item", "Item");
        # Entity
        $mot = $player->getDirectionVector()->multiply(2);
        $nbt = self::createBaseNBT($player->getPosition(), $mot, lcg_value() * 360, 0);
        $nbt->setShort("Health", 5);
        $nbt->setShort("PickupDelay", 10);
        $nbt->setTag("gunType", $itemTag);
        $entity = new BulletEntity($player->getLocation(), $ammo, $nbt);
        $entity->exempt = $player;
        $entity->gunType = $gunType;
        $entity->spawnToAll();
        return true;
    }

        /**
         * @param Player $player
         */
    public static function toggleGun(Player $player){
        if(isset(self::$tasks[spl_object_hash($player)])){
            self::$tasks[spl_object_hash($player)]->getHandler()->cancel();
            unset(self::$tasks[spl_object_hash($player)]);
        }else{
            $gun = $player->getInventory()->getItemInHand();
            $gunType = $gun->getCustomBlockData()->getString("gunType");
            $task = new FireGunTask($player, $gun);
            Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, GunData::FIRE_RATES[$gunType]);
            self::$tasks[spl_object_hash($player)] = $task;
        }
    }

    /**
     * @param Vector3 $pos
     * @param Vector3|null $motion
     * @param float $yaw
     * @param float $pitch
     * @return CompoundTag
     */
    private static function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0): CompoundTag{
        return CompoundTag::create()->setTag("Pos", new ListTag([
                new DoubleTag($pos->x),
                new DoubleTag($pos->y),
                new DoubleTag($pos->z)
            ]))->setTag("Motion", new ListTag([
                new DoubleTag($motion !== null ? $motion->x : 0.0),
                new DoubleTag($motion !== null ? $motion->y : 0.0),
                new DoubleTag($motion !== null ? $motion->z : 0.0)
            ]))->setTag("Rotation", new ListTag([
                new FloatTag($yaw),
                new FloatTag($pitch)
            ]));
    }
}