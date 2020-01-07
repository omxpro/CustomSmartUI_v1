<?php

namespace nlog\CustomSmartUI;

use nlog\SmartUI\FormHandlers\forms\functions\CalendarFunction;
use nlog\SmartUI\FormHandlers\forms\functions\IslandMoveFunction;
use nlog\SmartUI\FormHandlers\forms\functions\ReceiveMoneyFunction;
use nlog\SmartUI\FormHandlers\forms\functions\SendMoneyFunction;
use nlog\SmartUI\FormHandlers\forms\functions\ShowMoneyInfoFunction;
use nlog\SmartUI\FormHandlers\forms\functions\SpawnFunction;
use nlog\SmartUI\FormHandlers\forms\functions\TellFunction;
use nlog\SmartUI\FormHandlers\forms\functions\WarpFunction;
use nlog\SmartUI\FormHandlers\forms\MainMenu;
use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase implements Listener {

    private static $form_id = 2594;
    private static $list_form_id = 67321;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $e) {
        $pl = $e->getOrigin()->getPlayer();
        $pk = $e->getPacket();
        if ($pk instanceof ModalFormResponsePacket) {
            $id = $pk->formId;
            if (intval($id / 10) === self::$form_id) {
                switch (json_decode($pk->formData)) {
                    case null:
                        return;
                    case false:
                        return;
                }
                switch ($id % 10) {
                    case 1: //red
                        $res = [
                                SpawnFunction::getIdentifyName() => 1,
                                WarpFunction::getIdentifyName() => 1
                        ];
                        break;
                    case 2: //green
                        $res = [
                                SpawnFunction::getIdentifyName() => 1,
                                WarpFunction::getIdentifyName() => 1,
                                SendMoneyFunction::getIdentifyName() => 1,
                                ReceiveMoneyFunction::getIdentifyName() => 1,
                                IslandMoveFunction::getIdentifyName() => 1
                        ];
                        break;
                    case 7: //cacao
                        $res = [
                                SpawnFunction::getIdentifyName() => 1,
                                WarpFunction::getIdentifyName() => 1,
                                SendMoneyFunction::getIdentifyName() => 1,
                                ReceiveMoneyFunction::getIdentifyName() => 1,
                                IslandMoveFunction::getIdentifyName() => 1,
                                CalendarFunction::getIdentifyName() => 1,
                                ShowMoneyInfoFunction::getIdentifyName() => 1
                        ];
                        break;
                    case 8: //blue
                        $res = [
                                SpawnFunction::getIdentifyName() => 1,
                                WarpFunction::getIdentifyName() => 1,
                                SendMoneyFunction::getIdentifyName() => 1,
                                ReceiveMoneyFunction::getIdentifyName() => 1,
                                IslandMoveFunction::getIdentifyName() => 1,
                                CalendarFunction::getIdentifyName() => 1,
                                ShowMoneyInfoFunction::getIdentifyName() => 1,
                                TellFunction::getIdentifyName() => 1
                        ];
                        break;
                    default:
                        return;
                }
                $pk = new ModalFormRequestPacket();
                $pk->formId = self::$list_form_id;

                $json = [];
                $json['type'] = 'form';
                $json['title'] = "§f§l원하시는 기능을 선택하세요.";
                $json['content'] = "";
                $json["buttons"] = [];
                foreach (array_filter(array_map(function (SmartUIForm $func) use ($res) {
                    return isset($res[$func::getIdentifyName()]) ? $func : null;
                }, SmartUI::getInstance()->getFormManager()->getFunctions())) as $f) {
                    /** @var SmartUIForm $f */
                    $json['buttons'][] = ['text' => "§f< " . $f->getName() . " >"];
                }

                $pk->formData = json_encode($json);

                $pl->getNetworkSession()->sendDataPacket($pk);
            } elseif ($id === self::$list_form_id) {
                $index = json_decode($pk->formData);
                $res = [
                        SpawnFunction::getIdentifyName() => 1,
                        WarpFunction::getIdentifyName() => 1,
                        SendMoneyFunction::getIdentifyName() => 1,
                        ReceiveMoneyFunction::getIdentifyName() => 1,
                        IslandMoveFunction::getIdentifyName() => 1,
                        CalendarFunction::getIdentifyName() => 1,
                        ShowMoneyInfoFunction::getIdentifyName() => 1,
                        TellFunction::getIdentifyName() => 1
                ];
                if ($index === null || ($index |= 0) >= count($res)) {
                    return;
                }
                array_values(array_filter(array_map(function (SmartUIForm $func) use ($res) {
                    return isset($res[$func::getIdentifyName()]) ? $func : null;
                }, SmartUI::getInstance()->getFormManager()->getFunctions())))[$index]->sendPacket($pl);
            }
        }
    }

    public function onUseItem(PlayerInteractEvent $ev) {
        $dmg = $ev->getItem()->getMeta();
        if ($ev->getItem()->getId() !== ItemIds::DYE || !($dmg === 17 || $dmg === 1 || $dmg === 2 || $dmg === 18)) {
            return;
        }
        $main = new MainMenu(SmartUI::getInstance(), SmartUI::getInstance()->getFormManager(), self::$form_id * 10 + $ev->getItem()->getMeta() % 10);
        $main->sendPacket($ev->getPlayer());
    }

}

