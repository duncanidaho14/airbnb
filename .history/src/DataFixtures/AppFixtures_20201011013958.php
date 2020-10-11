<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Annonce;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('Fr-fr');

        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('kirua')
                    ->setLastName('zoldik')
                    ->setEmail('kirua@gmail.com')
                    ->setHash($this->encoder->encodePassword($adminUser, 'password'))
                    ->setPicture('https://www.nautiljon.com/images/perso/00/08/kirua_zoldyck_10080.jpg?1521322922')
                    ->setIntroduction($faker->sentence())
                    ->setDescription('<p>'.join('</p><p>', $faker->paragraphs(3)) .'</p>')
                    ->addUserRole($adminRole);
        $manager->persist($adminUser);

        //Nous gérons les utilisateurs
        $users = [];
        $genres = ['male', 'female'];

        for ($i=0; $i <= 10; $i++) { 
            $user = new User();

            $genre = $faker->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';

            $picture .= ($genre == 'male' ? 'men/' : 'women/'). $pictureId;

            $hash = $this->encoder->encodePassword($user, 'password');

            $user->setFirstName($faker->firstname($genre))
                ->setLastName($faker->lastname)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence())
                ->setDescription('<p>'.join('<p></p>', $faker->paragraphs(3)). '</p>')
                ->setHash($hash)
                ->setPicture($picture)
            ;
            
            $manager->persist($user);
            $users[] = $user;
        }
        //Nous gérons les annonces
        for ($i=1; $i < 30; $i++) { 
            $annonce = new Annonce();

            
            $title = $faker->sentence();
            $coverImage = $faker->imageUrl(500, 500);
            $introduction = $faker->paragraph(2);
            $content = '<p>'.join('</p><p>', $faker->paragraphs(5)) . '</p>';


            $user = $users[mt_rand(0, count($users) -1)];

            $annonce->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(40, 200))
                ->setRooms(mt_rand(1, 5))
                ->setAuthor($user)
                ->setStreet($faker->streetAddress)
                ->setZip($faker->postcode)
                ->setLat($faker->latitude)
                ->setLon($faker->longitude)
                ;
            for ($j=0; $j <= mt_rand(2, 5) ; $j++) { 
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                      ->setCaption($faker->sentence())
                      ->setAnnonce($annonce);

                $manager->persist($image);
            }

            //Gestion des réservations
            for ($j=0; $j <= \mt_rand(0, 10) ; $j++) { 
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3 months');
                // Gestion de la date de fin
                $duration = mt_rand(3, 10);

                // clone la startDate pour la modifier
                $endDate = (clone $startDate)->modify("+$duration days");
                $amount = $annonce->getPrice() * $duration;

                $booker = $users[mt_rand(0, count($users) -1)];
                
                $comment = $faker->paragraph();

                $booking->setBooker($booker)
                        ->setAnnonce($annonce)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setCreatedAt($createdAt)
                        ->setAmount($amount)
                        ->setComment($comment)
                ;
                
                $manager->persist($booking);

                // Gestion des commentaires
                if (mt_rand(0, 1)) {
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(1, 5))
                            ->setAuthor($booker)
                            ->setAnnonce($annonce);
                    
                    $manager->persist($comment);
                }
            }
            $manager->persist($annonce);
        }
        

        $manager->flush();
    }
}
