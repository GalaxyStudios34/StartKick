<?php

namespace Cinnec\startkick\tasks;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use Cinnec\startkick\Main;

class StartKickVoteTask extends Task{

    public function __construct(Main $main, string $playername, $index, $reason){
        $this->main = $main;
        $this->playername = $playername;
        $this->index = $index;
        $this->reason = $reason;
    }

    public function onRun(): void
    {
        $player = $this->getOwner()->getServer()->getPlayerByPrefix($this->playername);
        if($player instanceof Player){

            $this->getOwner()->getServer()->broadcastMessage($this->getOwner()->getConfigs("message.yml")->get("startKickEnd"));
            $yesVotes = $this->getOwner()->getStatistics("yesVoteList");
            $noVotes = $this->getOwner()->getStatistics("noVoteList");
            $userArray = $this->getOwner()->getStatistics("onlinePlayer");
            $userName = $userArray[$this->index];
            $user = $this->getOwner()->getServer()->getPlayerByPrefix($userName);
            $this->getOwner()->getServer()->broadcastMessage("Die Ergebnisse sind §a" . $yesVotes . " Stimmen für §fden StartKick und §c" . $noVotes . " Stimmen dagegen." );
            if($yesVotes > $noVotes) {
                $user->kick($this->reason);
                $this->getOwner()->getServer()->broadcastMessage($userName . " wurde aufgrund der Stimmen der gekickt");
            } else {
                $this->getOwner()->getServer()->broadcastMessage($userName . " wurde aufgrund der Stimmen nicht gekickt");
            }
            $this->getOwner()->setStatistics();

        }
    }

    public function getOwner() : Main{
        return $this->main;
    }
}