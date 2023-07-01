<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\object\ItemEntity;

use pocketmine\data\SavedDataLoadingException;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\world\World;
# Libs
use Vecnavium\FormsUI\FormsUI;

use muqsit\simplepackethandler\SimplePacketHandler;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;

use DaPigGuy\libPiggyUpdateChecker\libPiggyUpdateChecker;
use fernanACM\Weapons\commands\WeaponCommand;
use fernanACM\Weapons\guns\entity\BulletEntity;
# My files
use fernanACM\Weapons\Event;

use fernanACM\Weapons\utils\PluginUtils;

class Loader extends PluginBase{

    /** @var Config $config */
    public Config $config;
    
    /** @var Loader $instance */
    private static Loader $instance;

    # CheckConfig
    public const CONFIG_VERSION = "1.0.0";

    /**
     * @return void
     */
    public function onLoad(): void{
        self::$instance = $this;
        $this->loadFiles();
    }

    /**
     * @return void
     */
    public function onEnable(): void{
        $this->loadCheck();
        $this->loadVirions();
        $this->loadCommands();
        $this->loadEvents();
    }

    /**
     * @return void
     */
    public function loadFiles(): void{
        # Config files
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml");
    }

    /**
     * @return void
     */
    public function loadCheck(): void{
        # CONFIG
        if((!$this->config->exists("config-version")) || ($this->config->get("config-version") != self::CONFIG_VERSION)){
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config_old.yml");
            $this->saveResource("config.yml");
            $this->getLogger()->critical("Your configuration file is outdated.");
            $this->getLogger()->notice("Your old configuration has been saved as config_old.yml and a new configuration file has been generated. Please update accordingly.");
        }
    }

    /**
     * @return void
     */
    public function loadCommands(): void{
        Server::getInstance()->getCommandMap()->register("weapons", new WeaponCommand());
    }

    /**
     * @return void
     */
    public function loadEvents(): void{
        # Event
        Server::getInstance()->getPluginManager()->registerEvents(new Event, $this);
        # Entity
        EntityFactory::getInstance()->register(BulletEntity::class, function(World $world, CompoundTag $nbt): BulletEntity{
            $itemTag = $nbt->getCompoundTag(ItemEntity::TAG_ITEM);
            if(is_null($itemTag)){
                throw new SavedDataLoadingException("Expected \"" . ItemEntity::TAG_ITEM . "\" NBT tag not found");
            }
            $item = Item::nbtDeserialize($itemTag);
            if($item->isNull()){
                throw new SavedDataLoadingException("Item is invalid");
            }
            return new BulletEntity(EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
        }, ['BulletEntity']);
    }

    /**
     * @return void
     */
    public function loadVirions(): void{
        foreach([
            "FormsUI" => FormsUI::class,
            "SimplePacketHandler" => SimplePacketHandler::class,
            "Commando" => BaseCommand::class,
            "libPiggyUpdateChecker" => libPiggyUpdateChecker::class
            ] as $virion => $class
        ){
            if(!class_exists($class)){
                $this->getLogger()->error($virion . " virion not found. Please download Weapons from Poggit-CI or use DEVirion (not recommended).");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }

        if(!PacketHooker::isRegistered()){
            PacketHooker::register($this);
        }
        # Update
        libPiggyUpdateChecker::init($this);
    }

    /**
     * @param Player $player
     * @param string $key
     * @return string
     */
    public static function getMessage(Player $player, string $key): string{
        $messageArray = self::$instance->config->getNested($key, []);
        if(!is_array($messageArray)){
            $messageArray = [$messageArray];
        }
        $message = implode("\n", $messageArray);
        return PluginUtils::codeUtil($player, $message);
    }

    /**
     * @return Loader
     */
    public static function getInstance(): Loader{
        return self::$instance;
    }

    /**
     * @return string
     */
    public static function Prefix(): string{
        return TextFormat::colorize(self::$instance->config->get("Prefix"));
    }
}