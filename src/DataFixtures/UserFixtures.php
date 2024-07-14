<?php

namespace App\DataFixtures;

use App\Entity\QrcodeEntity;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Faker\Factory as Faker;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker::create('fr_FR');

        // Create admin user
        $admin = new User();
        $admin->setFirstname(firstname: 'cyril');
        $admin->setEmail(email: 'c.lamy@papoel.fr');
        $plainPassword = 'admin';
        $admin->setPassword(password: $this->passwordHasher->hashPassword(user: $admin, plainPassword: $plainPassword));
        $admin->setRoles(roles: ['ROLE_ADMIN']);
        $manager->persist(object: $admin);

        // Create 10 users
        $users = [];
        for ($i = 1; $i <= 10; ++$i) {
            $user = new User();
            $user->setFirstname(firstname: $faker->firstName);
            $user->setEmail(email: 'email'.$i.'@papoel.fr');
            $plainPassword = 'user';
            $user->setPassword(password: $this->passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(roles: ['ROLE_USER']);
            $manager->persist(object: $user);
            $users[] = $user;
        }

        // Create 10 qrcodes
        $writer = new PngWriter();
        $projectDir = $this->parameterBag->get(name: 'kernel.project_dir');
        // $logoPath = sprintf('%s/assets/images/papoel.jpg', $projectDir);

        for ($i = 1; $i <= 10; ++$i) {
            $qrcode = new QrcodeEntity();
            $urls = [
                'https://www.papoel.fr',
                'https://www.h2o.papoel.fr',
                'https://www.cyrcrea.com',
                'https://mg-candles.fr',
            ];
            $url = $faker->randomElement($urls);

            $randomForegroundColor = new Color(
                red: $faker->numberBetween(int1: 1, int2: 255),
                green: $faker->numberBetween(int1: 1, int2: 255),
                blue: $faker->numberBetween(int1: 1, int2: 255)
            );

            $randomBackgroundColor = new Color(
                red: $faker->numberBetween(int1: 1, int2: 255),
                green: $faker->numberBetween(int1: 1, int2: 255),
                blue: $faker->numberBetween(int1: 1, int2: 255)
            );

            $qrCode = QrCode::create($url)
                ->setEncoding(encoding: new Encoding(value: 'UTF-8'))
                ->setSize(size: 250)
                ->setMargin(margin: 10)
                ->setForegroundColor($randomForegroundColor)
                ->setBackgroundColor($randomBackgroundColor);

            // $logo = Logo::create($logoPath)
            //     ->setResizeToWidth(resizeToWidth: 100);
            $label = Label::create(text: '')
                ->setFont(font: new NotoSans(size: 8));

            $filename = sprintf('qrcode_papoel_%d.png', $i);
            $uploadedDir = $this->parameterBag->get(name: 'upload_dir');
            $filePath = sprintf('%s/%s/%s', $projectDir, $uploadedDir, $filename);

            $qrcode->setData(data: $url);
            $qrcode->setType(type: 'png');
            $qrcode->setForegroundColor(foregroundColor: $this->convertRgbToHex((array) $randomForegroundColor));
            $qrcode->setBackgroundColor(backgroundColor: $this->convertRgbToHex((array) $randomBackgroundColor));
            $qrcode->setFilename(filename: $filename);
            $qrcode->setFilePath(filePath: $filePath);
            $qrcode->setUser(user: $faker->randomElement($users));

            // Write the QR code to a file
            $qrCodeDataUri = $writer->write(qrCode: $qrCode, label: $label)->saveToFile($filePath);

            $manager->persist($qrcode);
        }
        $manager->flush();
    }

    public function convertRgbToHex(array $color): string
    {
        $colorHex = '#';
        foreach ($color as $value) {
            $hex = dechex($value);
            $colorHex .= str_pad($hex, 2, '0', STR_PAD_LEFT); // Assure que chaque composante RGB est sur 2 caractères
        }

        // Assure que la chaîne hexadécimale ne dépasse pas 7 caractères au total (y compris '#')
        if (strlen($colorHex) > 7) {
            $colorHex = substr($colorHex, 0, 7); // Coupe la chaîne si elle est trop longue
        }

        return $colorHex;
    }
}
