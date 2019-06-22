<?php

/*
 * Copyright (c) 2019 tim03we  < https://github.com/tim03we >
 * Discord: tim03we | TP#9129
 *
 * This software is distributed under "GNU General Public License v3.0".
 * This license allows you to use it and/or modify it but you are not at
 * all allowed to sell this plugin at any cost. If found doing so the
 * necessary action required would be taken.
 *
 * SlashSkin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 */

declare(strict_types=1);

namespace tim03we\slashskin\cmd;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\Skin;
use pocketmine\Player;
use tim03we\slashskin\libs\jojoe77777\FormAPI\SimpleForm;
use tim03we\slashskin\Main;

class SkinCommand extends Command {

    public function __construct(Main $plugin) {
        parent::__construct("皮肤", "更换你的皮肤", "/skin", ["skin"]);
        $this->setPermission("slashskin.use");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            return false;
        }
        if($sender instanceof Player) {
            $this->openList($sender);
        } else {
            $sender->sendMessage($this->plugin->prefix . "请在游戏中使用此命令!");
        }
        return false;
    }

    public function openList($player) {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->openSkins($player);
                    break;
                case 1:
                    $player->setSkin(new Skin("Standard_Custom", $this->plugin->skins[$player->getName()]));
                    $player->sendSkin();
                    $player->sendMessage($this->plugin->prefix . $this->plugin->cfg->getNested("messages.skin-reset"));
            }
        });
        $form->setTitle($this->plugin->cfg->getNested("messages.forms.main-form.title"));
        $form->addButton($this->plugin->cfg->getNested("messages.forms.main-form.button-1"));
        $form->addButton($this->plugin->cfg->getNested("messages.forms.main-form.button-2"));
        $form->sendToPlayer($player);
    }

    public function openSkins($player) {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            $skin = $data;
            if(file_exists($this->plugin->getDataFolder() . $data . ".png")) {
                $player->setSkin(new Skin("Standard_Custom", $this->plugin->createSkin($skin)));
                $player->sendSkin();
                $player->sendMessage($this->plugin->prefix . str_replace("{name}", $skin, $this->plugin->cfg->getNested("messages.skin-success")));
            } else {
                $player->sendMessage($this->plugin->prefix . $this->plugin->cfg->getNested("messages.skin-not-exist"));
            }
        });
        $form->setTitle($this->plugin->cfg->getNested("messages.forms.skin-list-form.title"));
        $form->setContent($this->plugin->cfg->getNested("messages.forms.skin-list-form.description"));
        foreach ($this->plugin->cfg->get("skins") as $skin) {
            $form->addButton("$skin", -1, "", $skin);
        }
        $form->sendToPlayer($player);
    }
}