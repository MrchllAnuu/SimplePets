<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\command;

use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use brokiem\simplepets\SimplePets;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Command extends \pocketmine\command\Command implements PluginOwned {

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (isset($args[0])) {
            switch (strtolower($args[0])) {
                case "spawn":
                    if (!$sender->hasPermission("simplepets.spawn")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    if ($sender instanceof Player) {
                        if (isset($args[2])) {
                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[2]])) {
                                $sender->sendMessage("§cYou already have a pet with the name " . $args[2]);
                            } else {
                                if (isset(SimplePets::getInstance()->getPetManager()->getRegisteredPets()[$args[1]])) {
                                    if (isset($args[3]) && is_numeric($args[3]) and (float)$args[3] > 0 and (float)$args[3] < 10) {
                                        if (isset($args[4]) && is_bool($args[4])) {
                                            SimplePets::getInstance()->getPetManager()->spawnPet($sender, $args[1], $args[2], (float)$args[3], (bool)$args[4]);
                                        } else {
                                            SimplePets::getInstance()->getPetManager()->spawnPet($sender, $args[1], $args[2], (float)$args[3]);
                                        }
                                    } else {
                                        SimplePets::getInstance()->getPetManager()->spawnPet($sender, $args[1], $args[2]);
                                    }

                                    $sender->sendMessage("§b" . str_replace("Pet", " Pet", $args[1]) . " §awith the name §b" . $args[2] . " §ahas been successfully spawned");
                                } else {
                                    $sender->sendMessage("§cPet with type §4" . $args[1] . " §cis not registered");
                                }
                            }
                        } else {
                            $sender->sendMessage("§cUsage: /spet spawn <petType> <petName> <petSize> <petBaby>");
                        }
                    } else {
                        $sender->sendMessage("§cOnly player can run this command");
                    }
                    break;
                case "remove":
                case "delete":
                    if (!$sender->hasPermission("simplepets.remove")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]])) {
                                $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]];
                                $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                    $pet->despawn();
                                }

                                SimplePets::getInstance()->getPetManager()->removeActivePet($sender, $args[1]);
                                SimplePets::getInstance()->getDatabaseManager()->removePet($sender, $args[1]);
                                $sender->sendMessage("§aPet with the name §b" . $args[1] . " §ahas been successfully removed");
                            } else {
                                $sender->sendMessage("§aYou don't have a pet with the name §b" . $args[1]);
                            }
                        } else {
                            $sender->sendMessage("§cUsage: /spet remove <petName>");
                        }
                    }
                break;
                case "inventory":
                case "inv":
                    if (!$sender->hasPermission("simplepets.inv")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]])) {
                                $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]];
                                $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                    if ($pet->isInvEnabled()) {
                                        $pet->getInventoryMenu()->send($sender, $pet->getName());
                                    } else {
                                        $sender->sendMessage("§cInventory access to your pet named §4" . $pet->getName() . " §cis disabled");
                                    }
                                }
                            } else {
                                $sender->sendMessage("§aYou don't have a pet with the name §b" . $args[1]);
                            }
                        } else {
                            $sender->sendMessage("§cUsage: /spet inv <petName>");
                        }
                    }
                break;
                case "ride":
                    if (!$sender->hasPermission("simplepets.ride")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]])) {
                                $id = SimplePets::getInstance()->getPetManager()->getActivePets()[$sender->getName()][$args[1]];
                                $pet = Server::getInstance()->getWorldManager()->findEntity($id);

                                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                                    if ($pet->isRidingEnabled()) {
                                        $pet->link($sender);
                                    } else {
                                        $sender->sendMessage("§cRiding access to your pet named §4" . $pet->getName() . " §cis disabled");
                                    }
                                }
                            } else {
                                $sender->sendMessage("§aYou don't have a pet with the name §b" . $args[1]);
                            }
                        } else {
                            $sender->sendMessage("§cUsage: /spet ride <petName>");
                        }
                    }
                    break;
                case "petlist":
                    if (!$sender->hasPermission("simplepets.petlist")) {
                        $sender->sendMessage("§cYou don't have permission to run this command");
                        return;
                    }

                    $message = "§bSimplePets pet list:\n";

                    foreach (SimplePets::getInstance()->getPetManager()->getRegisteredPets() as $type => $class) {
                        $message .= "§b- §a" . $type . "\n";
                    }

                    $sender->sendMessage($message);
                    break;
                case "help":
                    $sender->sendMessage("\n§7---- ---- ---- - ---- ---- ----\n§eCommand List:\n§2» /spet petlist\n§2» /spet spawn <petType> <petName> <petSize>\n§2» /spet remove <petName>\n§2» /spet inv <petName>\n§2» /spet ride <petName>\n§7---- ---- ---- - ---- ---- ----");
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Subcommand '$args[0]' not found! Try '/spet help' for help.");
            }
        } else {
            $sender->sendMessage("§7---- ---- [ §aSimplePets§7 ] ---- ----\n§bAuthor: @brokiem\n§3Source Code: github.com/brokiem/SimplePets\nVersion " . $this->getOwningPlugin()->getDescription()->getVersion() . "\n§7---- ---- ---- - ---- ---- ----");
        }
    }

    public function getOwningPlugin(): Plugin {
        return SimplePets::getInstance();
    }
}