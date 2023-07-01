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
use fernanACM\Weapons\guns\GunData;
use fernanACM\Weapons\Loader;
use fernanACM\Weapons\guns\GunManager;
use fernanACM\Weapons\utils\PluginUtils;

class GiveGunSubCommand extends BaseSubCommand{

    public function __construct(){
        parent::__construct("guns", "Provide weapon to a player by fernanACM", ["gun"]);
        $this->setPermission("weapons.cmd.acm");
    }

    /**
     * @return void
     */
    protected function prepare(): void{
        $this->registerArgument(0, new RawStringArgument("type", true));
        $this->registerArgument(1, new IntegerArgument("amount", true));
        $this->registerArgument(2, new RawStringArgument("player", true));    
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

        if(!isset($args["type"])){
            $sender->sendMessage(Loader::Prefix(). "§cUsa: /weapons guns <type> <amount>");
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }

        $gun = strtolower($args["type"]);
        if(!isset($gun)){
            $sender->sendMessage(Loader::Prefix(). Loader::getMessage($sender, "Messages.weapon-invalid"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }

        if(!in_array($gun, GunData::GUN_LIST)){
            $sender->sendMessage(Loader::Prefix(). "§b$gun §cis not a known gun.");
            $sender->sendMessage(Loader::Prefix(). "§eGun list: §f" . implode(", ", GunData::GUN_LIST));
            PluginUtils::PlaySound($sender, "random.pop", 1, 1);
            return;
        }

        if(!isset($args["amount"])){
            $sender->sendMessage(Loader::Prefix(). "§cUsa: /weapons guns <type> <amount>");
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
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
        GunManager::giveGun($target, $gun, $amount);
        $target->sendMessage(Loader::Prefix(). Loader::getMessage($target, "Messages.successful-execution"));
        $sender->sendMessage(Loader::Prefix(). str_replace(["{TARGET}"], [$target->getName()], Loader::getMessage($sender, "Messages.successful-execution-target")));
        PluginUtils::PlaySound($target, "random.levelup", 1, 5.1);
    }
}