<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;

use pocketmine\block\BlockTypeIds;

use fernanACM\Weapons\Loader;
use fernanACM\Weapons\guns\GunData;
use fernanACM\Weapons\guns\GunManager;
use fernanACM\Weapons\utils\PluginUtils;

class Event implements Listener{

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     */
    public function onInteract(PlayerItemUseEvent $event): void{
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->hasCustomBlockData() && $item->getCustomBlockData()->getTag("gunType")){
            $gunType = $item->getCustomBlockData()->getString("gunType");
            if(in_array($gunType, GunData::FULL_AUTO)){
                GunManager::toggleGun($player);
            }else{
                if(GunManager::onFire($player, $item)){
                    $gunType = $item->getCustomBlockData()->getString("gunType");
                    PluginUtils::BroadSound($player, "firework.blast", 500, GunData::SHOT_PITCH[$gunType]);
                }else{
                    PluginUtils::BroadSound($player, "random.click", 500, 0.5);
                    $player->sendTip(Loader::getMessage($player, "Weapons.ammo.no-ammo-tip"));
                }
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     */
   public function onInteractTool(PlayerInteractEvent $event): void{
        $item = $event->getItem();
        $block = $event->getBlock();
        if($item->hasCustomBlockData() && $item->getCustomBlockData()->getTag("gunType")){
            if($block->getTypeId() === BlockTypeIds::DIRT || 
               $block->getTypeId() === BlockTypeIds::DIRT ||
               $block->getTypeId() === BlockTypeIds::GRASS){
                $event->cancel();
            }
        }
    }
}