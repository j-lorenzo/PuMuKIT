<?php

namespace Pumukit\NewAdminBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\UserBundle\Model\UserInterface;
use MongoDB\BSON\ObjectId;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Role;
use Pumukit\SchemaBundle\Document\User;

class UserStatsService
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function getUserMultimediaObjectsGroupByStats(UserInterface $user): array
    {
        $collection = $this->documentManager->getDocumentCollection(MultimediaObject::class);
        $code = 'owner';
        $pipeline = $this->generateUserFilterPipeline($user, $code);
        $pipeline[] = [
            '$group' => [
                '_id' => '$status',
                'multimediaObjects' => ['$addToSet' => '$_id'],
            ],
        ];

        return $collection->aggregate($pipeline, ['cursor' => []])->toArray();
    }

    public function getUserMultimediaObjectsGroupByRole(UserInterface $user): array
    {
        $result = [];
        $roles = $this->documentManager->getRepository(Role::class)->findAll();
        $multimediaObjectCollection = $this->documentManager->getDocumentCollection(MultimediaObject::class);
        foreach ($roles as $role) {
            $pipeline = $this->generateUserFilterPipeline($user, $role->getCod());
            $pipeline[] = [
                '$group' => [
                    '_id' => $role->getCod(),
                    'multimediaObjects' => ['$addToSet' => '$_id'],
                ],
            ];
            $result[$role->getCod()] = $multimediaObjectCollection->aggregate($pipeline, ['cursor' => []])->toArray();
        }

        return $result;
    }

    public function getUserStorageMB(UserInterface $user)
    {
        $collection = $this->documentManager->getDocumentCollection(MultimediaObject::class);
        $code = 'owner';
        $pipeline = $this->generateUserFilterPipeline($user, $code);
        $pipeline[] = ['$unwind' => '$tracks'];
        $pipeline[] = [
            '$project' => [
                'size' => ['$sum' => '$tracks.size'],
            ],
        ];
        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'size' => ['$sum' => '$size'],
            ],
        ];
        $result = $collection->aggregate($pipeline, ['cursor' => []])->toArray();

        return reset($result)['size'] / 1048576;
    }

    public function getUserUploadedHours(UserInterface $user)
    {
        $collection = $this->documentManager->getDocumentCollection(MultimediaObject::class);
        $code = 'owner';
        $pipeline = $this->generateUserFilterPipeline($user, $code);
        $pipeline[] = ['$unwind' => '$tracks'];
        $pipeline[] = [
            '$project' => [
                'duration' => ['$sum' => '$tracks.duration'],
            ],
        ];
        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'duration' => ['$sum' => '$duration'],
            ],
        ];
        $result = $collection->aggregate($pipeline, ['cursor' => []])->toArray();
        $seconds = reset($result)['duration'];

        return gmdate('H:i:s', $seconds);
    }

    private function generateUserFilterPipeline(User $user, string $code): array
    {
        return [
            [
                '$match' => [
                    'status' => ['$ne' => MultimediaObject::STATUS_PROTOTYPE],
                    'people' => [
                        '$elemMatch' => [
                            'cod' => $code,
                            'people._id' => new ObjectId($user->getPerson()->getId()),
                        ],
                    ],
                ],
            ],
        ];
    }
}
