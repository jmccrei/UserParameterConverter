<?php
/**
 * Copyright (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace Jmccrei\UserParameterConverter\Request;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserParamConverter
 *
 * @package App\Request
 */
class UserParamConverter implements ParamConverterInterface
{
    /**
     * @var Security
     */
    protected $security;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * UserParamConverter constructor.
     *
     * @param Security $security
     * @param EntityManagerInterface $em
     */
    public function __construct(Security $security,
                                KernelInterface $kernel,
                                EntityManagerInterface $em)
    {
        dump( $kernel->getContainer()->get( 'doctrine' ) );
        exit;
        $this->security = $security;
        $this->entityManager = $em;
    }

    /**
     * Apply
     *
     * @param Request $request
     * @param ParamConverter $configuration
     * @return bool|void
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (null === $user = $this->security->getUser()) {
            throw new LogicException('User not logged in');
        }

        $options = $this->getOptions($configuration);
        $object = null;

        if ('collection' === $options['user_bind']['type']) {

            $entityName = $options['user_bind']['entity'] ?? null;
            $userParamName = $options['user_bind']['userParam'] ?? 'user';

            $repo = $this->entityManager->getRepository($entityName);

            $object = $repo->findBy([$userParamName => $user]);

            if (!$object instanceof ArrayCollection) {
                if ($object instanceof Collection) {
                    $object = new ArrayCollection($object->toArray());
                } elseif (is_array($object)) {
                    $object = new ArrayCollection($object);
                }
            }
        } else {
            $class = $configuration->getClass();
            $repo = $this->entityManager->getRepository($class);
            $userParameter = $options['user_bind']['userParameter'] ?? 'user';
            $identifier = $options['user_bind']['primaryKey'] ?? 'id';
            $object = $repo->findOneBy([$userParameter => $user, 'id' => $request->get($identifier)]);

            if (empty($object)) {
                $nameParts = explode('\\', $class) ?? [];
                $name = array_pop($nameParts);
                throw new NotFoundHttpException(sprintf('`%s` not found for user', $name));
            }
        }

        $request->attributes->set($configuration->getName(), $object);

        // set class to null so doctrine doesn't pick it up
        $configuration->setClass(null);
    }

    /**
     * Get proper options with user_bind set to an array of values to consume
     *
     * @param ParamConverter $configuration
     * @return array
     */
    protected function getOptions(ParamConverter $configuration): array
    {
        $userBind = [
            'enabled' => true,
            'type' => 'single',
            'entity' => null,
            'userParameter' => 'user',
            'primaryKey' => 'id'
        ];

        $options = $configuration->getOptions() ?? [];

        if (isset($options['user_bind'])) {
            if (is_bool($options['user_bind'])) {
                $userBind['enabled'] = $options['user_bind'];
                $userBind['entity'] = $configuration->getClass();
                $options['user_bind'] = $userBind;
            } elseif (is_array($options['user_bind'])) {
                $options['user_bind'] = array_merge(
                    $userBind,
                    $options['user_bind']
                );
            }
        }

        return $options;
    }

    /**
     * Does this converter support the current ParamConverter settings?
     *
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        $result = false;
        $options = $configuration->getOptions() ?? [];
        if (isset($options['user_bind'])) {
            if (is_array($options['user_bind'])) {
                $result = (bool)$options['user_bind']['enabled'];
            } elseif (is_bool($options['user_bind'])) {
                $result = $options['user_bind'];
            }
        }

        return $result;
    }
}