<?php

declare(strict_types=1);

namespace Cinnec\startkick;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Cinnec\startkick\tasks\StartKickVoteTask;

class Main extends PluginBase{

    protected $onlinePlayer = [];
    protected $isAlreadyStartkick = false;
    protected $playerVoteList = [];
    protected $yesVoteList = 0;
    protected $noVoteList = 0;

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command){
            case "startkick":
                if($sender instanceof Player){
                    if($this->isAlreadyStartkick == false){
                        if($sender->hasPermission("startkick.perm")){
                            $this->startkickForm($sender);
                        } else {
                            $sender->sendMessage($this->getConfigs("message.yml")->get("wrongPermission"));
                        }
                    } else {
                        $sender->sendMessage($this->getConfigs("message.yml")->get("onlyOneStartKickPerTime"));
                    }
                }
                break;
            case "ja":
                if($sender instanceof Player){
                    if($this->isAlreadyStartkick == true){
                        if(!in_array($sender->getName(), $this->playerVoteList)){
                            $this->yesVoteList += 1;
                            $sender->sendMessage($this->getConfigs("message.yml")->get("successfulYes"));
                            array_push($this->playerVoteList, $sender->getName());
                        } else {
                            $sender->sendMessage($this->getConfigs("message.yml")->get("alreadyVotedMessage"));
                        }
                    } else {
                        $sender->sendMessage($this->getConfigs("message.yml")->get("noStartkick"));
                    }
                }
                break;
            case "nein":
                if($sender instanceof Player){
                    if($this->isAlreadyStartkick == true){
                        if(!in_array($sender->getName(), $this->playerVoteList)){
                            $this->noVoteList += 1;
                            $sender->sendMessage($this->getConfigs("message.yml")->get("successfulNo"));
                            array_push($this->playerVoteList, $sender->getName());
                        } else {
                            $sender->sendMessage($this->getConfigs("message.yml")->get("alreadyVotedMessage"));
                        }
                    } else {
                        $sender->sendMessage($this->getConfigs("message.yml")->get("noStartkick"));
                    }
                }
                break;
        }
        return true;
    }

    public function startkickForm($player){
        $this->getAllPlayers();
        $form = new CustomForm(function (Player $player, array $data = null ){
            if($data == null){
                return false;
            }
            $index = $data[0];
            $user = $this->onlinePlayer[$index];
            if($data[1] == null){
                $reason = "Es wurde keine Begründung für diesen Startkick angegeben";
            } else {
                $reason = $data[1];
            }

            $this->getServer()->broadcastMessage("§4§lNeuer Startkick");
            $this->getServer()->broadcastMessage("§fSoll der Spieler " . "§c" . $user . "§f gekickt werden? §a/ja §c/nein");
            $this->getServer()->broadcastMessage("§4Ersteller: " . "§f" . $player->getName());
            $this->getServer()->broadcastMessage("§4Begründung: " . "§f" . $reason);

            $this->isAlreadyStartkick = true;
            array_push($this->playerVoteList, $player->getName());
            $this->yesVoteList += 1;

            $player->sendMessage($this->getConfigs("message.yml")->get("successfulStart"));

            $task = new StartKickVoteTask($this, $player->getName(), $index, $reason);
            $this->getScheduler()->scheduleDelayedTask($task, 10*20);
        });
        $form->setTitle($this->getConfigs("message.yml")->get("startKickFormTitle"));
        $form->addDropdown("Spieler auswählen", $this->onlinePlayer);
        $form->addInput("Begründung");
        $player->sendForm($form);
        return $form;
    }

    public function getConfigs($file){
        $file = new Config($this->getDataFolder() . $file, CONFIG::YAML);
        return $file;
    }

    public function getAllPlayers(){
        if(!empty($this->onlinePlayer)) {
            $this->onlinePlayer = [];
        }
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $item){
            $this->onlinePlayer[] = $item->getName();
        }
    }

    public function getStatistics($variable){
        return $this->$variable;
    }

    public function setStatistics(){
        $this->yesVoteList = 0;
        $this->noVoteList = 0;
        $this->playerVoteList = [];
        $this->isAlreadyStartkick = false;
    }
}
