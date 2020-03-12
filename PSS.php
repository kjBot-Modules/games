<?php
namespace kjBotModule\kj415j45\games;

use DateTime;
use kjBot\Framework\DataStorage;
use kjBot\Framework\Module;
use kjBotModule\kj415j45\games\Result;
use kjBot\Framework\Event\MessageEvent;
use kjBotModule\kj415j45\CoreModule\Economy;

class PSS extends Module{
    const Error = -1;
    const Paper = 1;
    const Scissor = 2;
    const Stone = 3;
    const BaseDir = 'games.PSS/';

    public function process(array $args, MessageEvent $event){
        $bot = rand(0, 2);
        $user = PSS::ParsePSS($args[1]??q('你还没出手呢'));
        if($user === PSS::Error)q('无法理解你的手法');
        $result = PSS::Judge($user, $bot);
        switch($result){
            case Result::Win:
                $availableTimeData = DataStorage::GetData(static::BaseDir.$event->getId());
                if($availableTimeData === false){
                    $availableTime = new DateTime('-1 second');
                }else{
                    $availableTime = new DateTime($availableTimeData);
                }
                $now = new DateTime();
                if($now > $availableTime){
                    (new Economy($event->getId()))->addBalance(10);
                    $cooldown = (new DateTime('+1 hour'))->format('c');
                    DataStorage::SetData(static::BaseDir.$event->getId(), $cooldown);
                    return $event->sendBack('恭喜获胜，奖励 10 金币（一小时后刷新）');
                }else{
                    return $event->sendBack('恭喜获胜');
                }
            case Result::Draw: return $event->sendBack('平局');
            case Result::Lost: return $event->sendBack('遗憾，你输了');
        }
    }

    private static function Judge(int $A, int $B): int{
        switch($A){
            case PSS::Paper:
                switch($B){
                    case PSS::Paper: return Result::Draw;
                    case PSS::Scissor: return Result::Lost;
                    case PSS::Stone: return Result::Win;
                }
            case PSS::Scissor:
                switch($B){
                    case PSS::Paper: return Result::Win;
                    case PSS::Scissor: return Result::Draw;
                    case PSS::Stone: return Result::Lost;
                }
            case PSS::Stone:
                switch($B){
                    case PSS::Paper: return Result::Lost;
                    case PSS::Scissor: return Result::Win;
                    case PSS::Stone: return Result::Draw;
                }
        }
        return Result::Draw; //really?
    }

    private static function ParsePSS(string $str): int{
        switch($str){
            case '石头':
            case 'stone':
            case '拳头':
                return PSS::Stone;
            case '布':
            case 'paper':
            case '纸':
                return PSS::Paper;
            case '剪刀':
            case 'scissor':
                return PSS::Scissor;
        }
        return PSS::Error;
    }
}