<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons\commands\subcommands;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;

use fernanACM\Weapons\Loader;
use fernanACM\Weapons\guns\GunManager;
use fernanACM\Weapons\utils\PluginUtils;

class GiveAmmoSubCommand extends BaseSubCommand{

    public function __construct(){
        parent::__construct("ammo", "Provide ammo to a player by fernanACM", []);
        $this->setPermission("weapons.cmd.acm");
    }

    protected function prepare(): void{
        $this->registerArgument(0, new IntegerArgument("amount", true));
        $this->registerArgument(1, new RawStringArgument("player", true));    
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

        if(!isset($args["amount"])){
            $sender->sendMessage(Loader::Prefix(). "§cUsa: /weapons ammo <amount>");
            PluginUtils::PlaySound($sender, "random.pop", 1, 1);
            return;
        }

        $amount = $args["amount"] ?? 1;
        if(!is_numeric($amount)){
            $sender->sendMessage(Loader::Prefix(). Loader::getMessage($sender, "Messages.not-a-number"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }

        $target = empty($args["player"]) ? $sender : Server::getInstance()->getPlayerExact($args["player"]);
        if(!$target instanceof Player){
            $sender->sendMessage(Loader::Prefix(). Loader::getMessage($sender, "Messages.invalid-player"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }
        GunManager::giveAmmo($target, $amount);
        $target->sendMessage(Loader::Prefix(). Loader::getMessage($target, "Messages.successful-execution"));
        $sender->sendMessage(Loader::Prefix(). str_replace(["{TARGET}"], [$target->getName()], Loader::getMessage($sender, "Messages.successful-execution-target")));
        PluginUtils::PlaySound($target, "random.levelup", 1, 5.1);
    }
}