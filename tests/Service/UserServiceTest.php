<?php

namespace App\Tests\Service;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCreateUserPersistsAndFlushes(): void
    {
        $em = Mockery::mock(EntityManagerInterface::class);
        $repo = Mockery::mock(UserRepository::class);

        $em->shouldReceive('persist')->once();
        $em->shouldReceive('flush')->once();

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

        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('find')->andReturn($fakeUser);

        $em = Mockery::mock(EntityManagerInterface::class);

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

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('flush')->once();

        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('find')->andReturn($fakeUser);
        
        $service = new UserService($repo, $em);
        
        // Act
        $service->updateUser(['email' => '2@example.com'], 1);

        // Assert
        $this->assertSame('2@example.com', $fakeUser->getEmail());
    }

    public function testGetUserReturnsNullWhenNotFound(): void
    {
        // Arrange
        $repo = Mockery::mock(UserRepository::class);
        $repo->shouldReceive('find')->andReturn(null);
           
        $em = Mockery::mock(EntityManagerInterface::class);

        $service = new UserService($repo, $em);
        // Act
        $result = $service->getUser(1);
        
        // Assert
        $this->assertNull($result);
    }
}