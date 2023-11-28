<?php
namespace App\Service;

//use Psr\Cache\CacheItemInterface;
//use Symfony\Contracts\Cache\CacheInterface;
//use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Inpost\Resources;
use App\Entity\Inpost\ResourcesType;
use App\Entity\Inpost\Items;

class Inpost
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param  string $resource
     * @param  string $queryParam
     * @param  string $queryValue
     * @return array
     */
    public function getData(string $resource, string $queryParam, string $queryValue): array
    {
        $httpClient = HttpClient::create();
        $url = sprintf('https://api-shipx-pl.easypack24.net/v1/%s?%s=%s', $resource, $queryParam, $queryValue);

        $response = $httpClient->request('GET', $url);

        $jsonContent = $response->getContent();
        $data = json_decode($jsonContent, true);

        $resourceTypeRepository = $this->entityManager->getRepository(ResourcesType::class);
        $data['type'] = $resourceTypeRepository->findOneBy(['name' => $resource])->getId();
        $data['query_value'] = $queryValue;

        $recources = $this->serializer->deserialize(json_encode($data), 'App\Entity\Inpost\Resources', 'json');
        ;

        $items = [];
        if (isset($data['items'])) {
            $itemsData = $data['items'];
            foreach ($itemsData as &$itemData) {
                $itemData['address'] = json_encode($itemData['address_details'], JSON_UNESCAPED_UNICODE);
            }

            $items = $this->serializer->deserialize(json_encode($itemsData), 'App\Entity\Inpost\Items[]', 'json');
        }

        return [
            'resources' => $recources,
            'items' => $items,
        ];
    }

    /**
     * @param  array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $resourceRepository = $this->entityManager->getRepository(Resources::class);
        $resourceEntity = new Resources();
        $resourceEntity->setCount($data['resources']->getCount());
        $resourceEntity->setPage($data['resources']->getPage());
        $resourceEntity->setTotalPages($data['resources']->getTotalPages());
        $resourceEntity->setType($data['resources']->getType());
        $resourceEntity->setQueryValue($data['resources']->getQueryValue());

        $existingResources = $resourceRepository->findOneBy(['queryValue' => $data['resources']->getQueryValue(), 'type' => $data['resources']->getType()]);
        if (!$existingResources) {
            $this->entityManager->persist($resourceEntity);
        }

        $itemsRepository = $this->entityManager->getRepository(Items::class);
        foreach ($data['items'] as $itemData) {
            // Check if an identical entry already exists
            $existingItem = $itemsRepository->findOneBy(['name' => $itemData->getName(), 'address' => $itemData->getAddress()]);
            if (!$existingItem) {
                $itemEntity = new Items();
                $itemEntity->setName($itemData->getName());
                $itemEntity->setAddress($itemData->getAddress());
                $itemEntity->setResource($resourceEntity->getId());

                $this->entityManager->persist($itemEntity);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param  array $criteria
     * @return array
     */
    public function findItemsByCriteria(array $criteria): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('i.id', 'i.name', 'i.address', 'i.resource')
            ->from(Items::class, 'i');

        foreach ($criteria as $field => $value) {
            if ($value !== null) {
                switch ($field) {
                    case 'city':
                        $queryBuilder->andWhere("i.address LIKE :city");
                        $queryBuilder->setParameter('city', '%"city":"%' . $value . '%"%');
                        break;
                    case 'postal_code':
                        $queryBuilder->andWhere("i.address LIKE :post_code");
                        $queryBuilder->setParameter('post_code', '%"post_code":"%' . $value . '%"%');
                        break;
                    case 'street':
                        $queryBuilder->andWhere("i.address LIKE :street");
                        $queryBuilder->setParameter('street', '%"street":"%' . $value . '%"%');
                        break;
                }
            }
        }

        $query = $queryBuilder->getQuery();
        $items = $query->getResult();
        foreach ($items as &$item) {
            $item['address'] = json_decode($item['address'], true);
        }

        return $items;
    }
}