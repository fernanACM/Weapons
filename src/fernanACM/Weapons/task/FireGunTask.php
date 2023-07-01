<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons\task;

use pocketmine\player\Player;

use pocketmine\scheduler\Task;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;

use fernanACM\Weapons\Loader;
use fernanACM\Weapons\guns\GunData;
use fernanACM\Weapons\guns\GunManager;
use fernanACM\Weapons\utils\PluginUtils;

class FireGunTask extends Task{

    /** @var Player $player */
    protected $player;

    /** @var Item $gun */
    protected $gun;

    /** @var Item $ammo */
    protected $ammo;

    /** @var int $amount */
    protected $amount = 0;
    /** @var int $slot */
    protected $slot = 0;

    public function __construct(Player $player, Item $item){
        $this->player = $player;
        $this->gun = $item;
        $ammo = $this->findAmmo();
        if($ammo !== false){
            $this->ammo = $ammo;
        }
    }

    /**
     * @return void
     */
    public function onRun(): void{
        if($this->amount <= 0){
            if(!is_null($this->ammo)){
                if($this->ammo->getCount() > 1){
                    $this->player->getInventory()->setItem($this->slot, $this->ammo->setCount($this->ammo->getCount() - 1));
                }else{
                    $this->ammo->setCustomBlockData(CompoundTag::create()->setInt("ammoAmount", $this->amount));
                    $this->player->getInventory()->setItem($this->slot, $this->ammo->setCount($this->ammo->getCount() - 1));
                }
            }
            $ammo = $this->findAmmo();
            if($ammo !== false){
                 $this->ammo = $ammo;
            }else{
                PluginUtils::BroadSound($this->player, "random.click", 500, 0.5);
                $this->player->sendTip(Loader::getMessage($this->player, "Weapons.ammo.no-ammo-tip"));
                $this->cancel();
                return;
            }
        }
        if(!$this->player->getInventory()->getItemInHand()->equals($this->gun)){
            $this->cancel();
            return;
        }
        GunManager::onFire($this->player, $this->gun, $this->ammo);
        $gunType = $this->gun->getCustomBlockData()->getString("gunType");
        $tipAmount = str_replace(["{AMOUNT}"], [$this->amount], Loader::getMessage($this->player, "Weapons.ammo.ammo-tip"));
        $this->player->sendTip($tipAmount);
        PluginUtils::BroadSound($this->player, "firework.blast", 500, GunData::SHOT_PITCH[$gunType]);
        --$this->amount;
    }

    /**
     * @return void
     */
    public function onCancel(): void{
        if(!is_null($this->ammo)){
            $this->ammo->setCustomBlockData(CompoundTag::create()->setInt("ammoAmount", $this->amount));
            $this->player->getInventory()->setItem($this->slot, $this->ammo);
        }
    }

    /**
     * @return void
     */
    protected function cancel(): void{
        GunManager::toggleGun($this->player);
    }

    /**
     * @return Item|boolean
     */
    protected function findAmmo(): Item|bool{
        foreach($this->player->getInventory()->getContents() as $slot => $item){
            if($item->hasCustomBlockData() && $item->getCustomBlockData()->getTag("ammoAmount")){
                $this->slot = $slot;
                $this->amount = $item->getCustomBlockData()->getInt("ammoAmount");
                return $item;
            }
        }
        return false;
    }
}