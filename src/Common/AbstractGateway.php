<?php
/**
 * Base payment gateway class
 */

namespace PHPAccounting\Common;

use PHPAccounting\Common\Http\Client;
use PHPAccounting\Common\Http\ClientInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Base payment gateway class
 *
 * This abstract class should be extended by all payment gateways
 * throughout the Omnipay system.  It enforces implementation of
 * the GatewayInterface interface and defines various common attibutes
 * and methods that all gateways should have.
 *
 * Example:
 *
 * <code>
 *   // Initialise the gateway
 *   $gateway->initialize(...);
 *
 *   // Get the gateway parameters.
 *   $parameters = $gateway->getParameters();
 *
 *   // Create a credit card object
 *   $card = new CreditCard(...);
 *
 *   // Do an authorisation transaction on the gateway
 *   if ($gateway->supportsAuthorize()) {
 *       $gateway->authorize(...);
 *   } else {
 *       throw new \Exception('Gateway does not support authorize()');
 *   }
 * </code>
 *
 * For further code examples see the *omnipay-example* repository on github.
 *
 */
abstract class AbstractGateway implements GatewayInterface
{
    use ParametersTrait;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $httpRequest;

    /**
     * Create a new gateway instance
     *
     * @param ClientInterface          $httpClient  A HTTP client to make API calls with
     * @param HttpRequest     $httpRequest A Symfony HTTP request object
     */
    public function __construct(ClientInterface $httpClient = null, HttpRequest $httpRequest = null)
    {
        $this->httpClient = $httpClient ?: $this->getDefaultHttpClient();
        $this->httpRequest = $httpRequest ?: $this->getDefaultHttpRequest();
        $this->initialize();
    }

    /**
     * Get the short name of the Gateway
     *
     * @return string
     */
    public function getShortName()
    {
        return Helper::getGatewayShortName(get_class($this));
    }

    /**
     * Initialize this gateway with default parameters
     *
     * @param  array $parameters
     * @return $this
     */
    public function initialize(array $parameters = array())
    {
        $this->parameters = new ParameterBag;

        // set default parameters
        foreach ($this->getDefaultParameters() as $key => $value) {
            if (is_array($value)) {
                $this->parameters->set($key, reset($value));
            } else {
                $this->parameters->set($key, $value);
            }
        }

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return array();
    }

    /**
     * @return boolean
     */
    public function getTestMode()
    {
        return $this->getParameter('testMode');
    }

    /**
     * @param  boolean $value
     * @return $this
     */
    public function setTestMode($value)
    {
        return $this->setParameter('testMode', $value);
    }

    /**
     * Supports Create
     *
     * @return boolean True if this gateway supports the authorize() method
     */
    public function supportsCreate()
    {
        return method_exists($this, 'create');
    }

    /**
     * Supports Update
     *
     * @return boolean True if this gateway supports the authorize() method
     */
    public function supportsUpdate()
    {
        return method_exists($this, 'update');
    }

    /**
     * Supports Get
     *
     * @return boolean True if this gateway supports the authorize() method
     */
    public function supportsGet()
    {
        return method_exists($this, 'get');
    }

    /**
     * Supports Delete
     *
     * @return boolean True if this gateway supports the authorize() method
     */
    public function supportsDelete()
    {
        return method_exists($this, 'delete');
    }


    /**
     * Supports Authorize
     *
     * @return boolean True if this gateway supports the authorize() method
     */
    public function supportsEntity()
    {
        return method_exists($this, 'entity');
    }

    /**
     * Create and initialize a request object
     *
     * This function is usually used to create objects of type
     * PHPAccounting\Common\Message\AbstractRequest (or a non-abstract subclass of it)
     * and initialise them with using existing parameters from this gateway.
     *
     * Example:
     *
     * <code>
     *   class MyRequest extends \PHPAccounting\Common\Message\AbstractRequest {};
     *
     *   class MyGateway extends \PHPAccounting\Common\AbstractGateway {
     *     function myRequest($parameters) {
     *       $this->createRequest('MyRequest', $parameters);
     *     }
     *   }
     *
     *   // Create the gateway object
     *   $gw = Omnipay::create('MyGateway');
     *
     *   // Create the request object
     *   $myRequest = $gw->myRequest($someParameters);
     * </code>
     *
     * @param string $class The request class name
     * @param array $parameters
     * @return \PHPAccounting\Common\Message\AbstractRequest
     */
    protected function createRequest($class, array $parameters)
    {
        $obj = new $class($this->httpClient, $this->httpRequest);

        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }

    /**
     * Get the global default HTTP client.
     *
     * @return ClientInterface
     */
    protected function getDefaultHttpClient()
    {
        return new Client();
    }

    /**
     * Get the global default HTTP request.
     *
     * @return HttpRequest
     */
    protected function getDefaultHttpRequest()
    {
        return HttpRequest::createFromGlobals();
    }
}
