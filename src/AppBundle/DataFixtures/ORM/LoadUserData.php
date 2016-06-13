<?php
/**
 * Created by PhpStorm.
 * User: sonic
 * Date: 13/06/16
 * Time: 13.35
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('riccardo');
        $user->setEmail('riccardo.sartori87@gmail.com');
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]));

        $manager->persist($user);
        $manager->flush();
    }
}