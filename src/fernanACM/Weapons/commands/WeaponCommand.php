<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons\commands;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseCommand;

use Vecnavium\FormsUI\CustomForm;

use fernanACM\Weapons\Loader;

use fernanACM\Weapons\guns\GunData;
use fernanACM\Weapons\guns\GunManager;
use fernanACM\Weapons\utils\PluginUtils;

use fernanACM\Weapons\commands\subcommands\GiveAmmoSubCommand;
use fernanACM\Weapons\commands\subcommands\GiveGunSubCommand;

class WeaponCommand extends BaseCommand{

    public function __construct(){
        parent::__construct(Loader::getInstance(), "weapons", "Assorted weapons by fernanACM", ["weap"]);
        $this->setPermission("weapons.cmd.acm");
    }

    /**
     * @return void
     */
    protected function prepare(): void{
        $this->registerSubCommand(new GiveGunSubCommand());
        $this->registerSubCommand(new GiveAmmoSubCommand());
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(!$sender instanceof Player){
            $sender->sendMessage("Use this command in-game");
            return;
        }

        if(!$sender->hasPermission("weapons.cmd.acm")){
            $sender->sendMessage(Loader::Prefix(). Loader::getMessage($sender, "Messages.no-permission"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }
        $this->selectWeapon($sender);
        PluginUtils::PlaySound($sender, "random.pop2", 1, 5.1);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function selectWeapon(Player $player): void{
        $custom = new CustomForm(function(Player $player, $data){
            if(is_null($data)){
                return true;
            }

            if(empty($data[1]) || !is_numeric($data[1]) || empty($data[2]) || !is_numeric($data[2])){
                $player->sendMessage(Loader::Prefix() . Loader::getMessage($player, "Messages.not-a-number"));
                PluginUtils::PlaySound($player, "mob.villager.no", 1, 1);
                return;
            }

            $target = Server::getInstance()->getPlayerExact($data[3]);
            if(empty($target) || !$target instanceof Player){
                $player->sendMessage(Loader::Prefix(). Loader::getMessage($player, "Messages.invalid-player"));
                PluginUtils::PlaySound($player, "mob.villager.no", 1, 1);
                return;
            }

            $weapon = GunData::GUN_LIST[$data[0]];
            GunManager::giveGun($target, $weapon, $data[1]);
            GunManager::giveAmmo($target, $data[2]);
            $target->sendMessage(Loader::Prefix(). Loader::getMessage($target, "Messages.successful-execution"));
            $player->sendMessage(Loader::Prefix(). str_replace(["{TARGET}"], [$target->getName()], Loader::getMessage($player, "Messages.successful-execution-target")));
            PluginUtils::PlaySound($player, "random.levelup", 1, 5.1);
        });
        $custom->setTitle(Loader::getMessage($player, "Form.title"));
        $custom->addDropdown(Loader::getMessage($player, "Form.content.gun-list"), GunData::GUN_LIST);
        $custom->addInput(Loader::getMessage($player, "Form.content.number-of-weapons"), " ", 1);
        $custom->addInput(Loader::getMessage($player, "Form.content.amount-of-ammo"), " ", 30);
        $custom->addInput(Loader::getMessage($player, "Form.content.player-name"), " ", $player->getName());
        $player->sendForm($custom);
    }
}