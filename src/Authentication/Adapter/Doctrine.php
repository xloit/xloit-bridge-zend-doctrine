<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * <http://www.opensource.org/licenses/mit-license.php>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   MIT
 * @link      http://xloit.com
 * @copyright Copyright (c) 2016, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Zend\Authentication\Adapter;

use Xloit\Bridge\Zend\Authentication\AuthenticationResult;
use Xloit\Bridge\Zend\Authentication\Exception;
use Xloit\Bridge\Zend\Authentication\Options\DoctrineOptions;
use Zend\Crypt\Password\PasswordInterface;

/**
 * A {@link Doctrine} class
 *
 * @package Xloit\Bridge\Zend\Authentication\Adapter
 */
class Doctrine extends AbstractAdapter
{
    /**
     *
     *
     * @param  array|DoctrineOptions $options
     *
     * @return static
     */
    public function setOptions($options)
    {
        if (!($options instanceof DoctrineOptions)) {
            $options = new DoctrineOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     *
     *
     * @return DoctrineOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Performs an authentication attempt
     *
     * @return AuthenticationResult
     * @throws \Zend\Crypt\Password\Exception\InvalidArgumentException
     * @throws \Xloit\Bridge\Zend\Authentication\Exception\RuntimeException
     */
    public function authenticate()
    {
        $this->setup();

        $options    = $this->getOptions();
        $identities = $options->getObjectRepository()
                              ->findBy([$options->getIdentityProperty() => $this->identity]);

        if (!count($identities)) {
            $this->storeAuthenticationResult(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND);

            return $this->authenticateCreateAuthResult();
        }

        $identity = array_shift($identities);

        if (is_array($identities) && count($identities) > 0) {
            $this->storeAuthenticationResult(AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS);

            return $this->authenticateCreateAuthResult();
        }

        return $this->validateIdentity($identity);
    }

    /**
     * This method attempts to validate that the record in the result set is indeed a record that matched the identity
     * provided to this adapter.
     *
     * @param  mixed $identity
     *
     * @return AuthenticationResult
     * @throws \Xloit\Bridge\Zend\Authentication\Exception\RuntimeException
     * @throws \Zend\Crypt\Password\Exception\InvalidArgumentException
     */
    protected function validateIdentity($identity)
    {
        $options            = $this->getOptions();
        $metadata           = $this->getOptions()->getClassMetadata();
        $credentialProperty = $options->getCredentialProperty();

        if (!$metadata->hasField($credentialProperty)) {
            throw new Exception\RuntimeException(
                sprintf(
                    'Property (%s) in (%s) is not accessible.',
                    $credentialProperty,
                    get_class($identity)
                )
            );
        }

        $credential      = $metadata->getFieldValue($identity, $credentialProperty);
        $credentialValue = $this->credential;
        $cryptService    = $options->getCryptService();

        if ($cryptService instanceof PasswordInterface) {
            if ($cryptService->verify($credentialValue, $credential)) {
                $this->storeAuthenticationResult(AuthenticationResult::SUCCESS, $identity);
            } else {
                $this->storeAuthenticationResult(AuthenticationResult::FAILURE_CREDENTIAL_INVALID);
            }
        } elseif ($credentialValue !== true && $credentialValue !== $credential) {
            $this->storeAuthenticationResult(AuthenticationResult::FAILURE_CREDENTIAL_INVALID);
        } else {
            $this->storeAuthenticationResult(AuthenticationResult::SUCCESS, $identity);
        }

        return $this->authenticateCreateAuthResult();
    }
}
