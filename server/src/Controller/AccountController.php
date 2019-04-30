<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Auth;
use App\Service\Validator;
use Doctrine\ORM\EntityManager;
use Respect\Validation\Validator as v;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Class AccountController
 * @package App\Controller
 */
class AccountController extends BaseController
{
    /**
     * Update User Account Details
     *
     * @Route("/account", methods={"PUT"})
     *
     * @param Request $request
     * @param Validator $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \App\Exception\HttpBadRequestException
     * @throws \App\Exception\HttpConflictException
     * @throws \App\Exception\ValidationException
     *
     * @SWG\Response(
     *     response=200,
     *     description="Update user account info"
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function update(Request $request, Validator $validator)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $requestData = $this->getRequestContent();

        $validator
            ->setValidator(
                v::stringType()->noWhitespace()->length(3),
                'firstName',
                'firstName must be a string type with minimum length of 3'
            )
            ->setValidator(
                v::noWhitespace()->email(),
                'email',
                'email must be a valid email address'
            )
            ->setValidator(
                v::stringType()->noWhitespace()->length(3),
                'lastName',
                'firstName must be a string type with minimum length of 3'
            )
            ->setValidator(
                v::stringType()->noWhitespace()->length(3),
                'password',
                'password must be a string type with minimum length of 3'
            )
            ->validate($requestData);

        /** @var Auth $identity */
        $identity = $this->getIdentity();

        /** @var User $user */
        $user = $em->getRepository(User::class)
            ->find($identity->getId());

        if (!$user) {
            throw new NotFoundHttpException('User not found!!');
        }

        $responseData = $this->deserialize($requestData, User::class, [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['roles', 'lastAccessTime'],
            AbstractNormalizer::OBJECT_TO_POPULATE => $user
        ]);

        return $this->setResponse($responseData, 200, [], [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']
        ]);
    }

    /**
     * Get User Account Details
     *
     * @Route("/account", methods={"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Get user account info"
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function view(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $serializer = ($request->get('serializer', null))
            ?: [
                'id', 'firstName', 'fullName', 'lastName', 'email', 'lastAccessTime', 'roles'
            ];

        /** @var Auth $identity */
        $identity = $this->getIdentity();

        /** @var User $user */
        $user = $em->getRepository(User::class)
            ->find($identity->getId());

        if (!$user) {
            throw new NotFoundHttpException('User not found!!');
        }

        return $this->setResponse($user, 200, [], [
            AbstractNormalizer::ATTRIBUTES => $serializer,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']
        ]);
    }
}