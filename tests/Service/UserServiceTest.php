<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testCreateUserPersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(UserRepository::class);

        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new UserService($repo, $em);

        $service->createUser([
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);
    }

    public function testGetUser(): void
    {
        // arrange
        $fakeUser = new User();
        $fakeUser->setEmail('test@example.com');

        $repo = $this->createStub(UserRepository::class);
        $repo->method('find')->willReturn($fakeUser);

        $em = $this->createStub(EntityManagerInterface::class);

        $service = new UserService($repo, $em);
        // Act
        $result = $service->getUser(1);
        // Assert
        $this->assertSame($fakeUser, $result);
    }

    public function testUpdateUser(): void
    {
        // Arrange
        $fakeUser = new User();
        $fakeUser->setEmail('1@example.com');
        $fakeUser->setFirstName('Test');
        $fakeUser->setLastName('Test');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $repo = $this->createStub(UserRepository::class);
        $repo->method('find')->willReturn($fakeUser);
        
        $service = new UserService($repo, $em);
        
        // Act
        $service->updateUser(['email' => '2@example.com'], 1);

        // Assert
        $this->assertSame('2@example.com', $fakeUser->getEmail());
    }

    public function testGetUserReturnsNullWhenNotFound(): void
    {
        // Arrange
        $repo = $this->createStub(UserRepository::class);
        $repo->method('find')->willReturn(null);
           
        $em = $this->createStub(EntityManagerInterface::class);

        $service = new UserService($repo, $em);
        // Act
        $result = $service->getUser(1);
        
        // Assert
        $this->assertNull($result);
    }
}