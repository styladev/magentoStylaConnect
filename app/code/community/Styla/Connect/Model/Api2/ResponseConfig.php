<?php

/**
 * Class Styla_Connect_Model_Api2_ResponseConfig
 */
class Styla_Connect_Model_Api2_ResponseConfig
{
    const FIELD_CONFIGURATION_XML = "default/styla_connect/response_fields/%s";

    const EVENT_PREPARE_RESPONSE = 'styla_connect_api_prepare_response';

    protected $_responseFields;

    /**
     * Take input Data Object or a Data Collection, and add fields required by styla
     * to it (or each element of it).
     *
     * Response type is the type of data you're processing - category, product...
     *
     * @param mixed  $requestedObject
     * @param string $responseType
     */
    public function prepareStylaApiResponse($requestedObject, $responseType)
    {
        $fieldConverters = $this->getConverters($responseType);

        if (!is_array($requestedObject)
            && !($requestedObject instanceof Varien_Data_Collection)
            && !($requestedObject instanceof Mage_Catalog_Model_Resource_Category_Tree)
        ) {
            $requestedObjects = array($requestedObject);
        } else {
            $requestedObjects = $requestedObject;

            //in case of a collection, we may wanna add some prequisites before trying to run the data conversion
            $this->_addConverterCollectionRequirements($fieldConverters, $requestedObjects);
        }


        Mage::dispatchEvent(
            self::EVENT_PREPARE_RESPONSE.'_before',
            array(
                'config'        => $this,
                'response_type' => $responseType,
                'objects'       => $requestedObjects,
            )
        );

        foreach ($requestedObjects as $object) {
            foreach ($fieldConverters as $converter) {
                $converter->runConverter($object);
            }
        }

        Mage::dispatchEvent(
            self::EVENT_PREPARE_RESPONSE.'_after',
            array(
                'config'        => $this,
                'response_type' => $responseType,
                'objects'       => $requestedObjects,
            )
        );
    }

    protected function _addConverterCollectionRequirements(array $currentConverters, $dataCollection)
    {
        $alreadyAddedRequirements = array();

        foreach ($currentConverters as $converter) {
            $requirementsId = $converter::REQUIREMENTS_TYPE;
            if (in_array($requirementsId, $alreadyAddedRequirements)) {
                continue;
            }

            $converter->addRequirementsToDataCollection($dataCollection);
            $alreadyAddedRequirements[] = $requirementsId;
        }
    }

    /**
     * Get all field converters defined for this response type
     *
     * @param string $responseType
     * @return array
     */
    public function getConverters($responseType)
    {
        $responseFieldsConfiguration = $this->_getConfiguredResponseFields($responseType);

        $responseFields = array();
        foreach ($responseFieldsConfiguration as $fieldName => $fieldConfiguration) {
            $converter = $this->getConverter($fieldConfiguration['class']);
            $converter->setArguments($fieldConfiguration['arguments']);

            $responseFields[$fieldName] = $converter;
        }

        return $responseFields;
    }

    /**
     *
     * @param string $responseType
     * @return array
     */
    protected function _getConfiguredResponseFields($responseType)
    {
        if (isset($this->_responseFields[$responseType])) {
            return $this->_responseFields[$responseType];
        }

        $fieldConfiguration = Mage::getConfig()->getNode(sprintf(self::FIELD_CONFIGURATION_XML, $responseType));
        if (!$fieldConfiguration) {
            Mage::throwException("Couldn't get field configuration for response type: ".$responseType);
        }

        $this->_responseFields[$responseType] = $fieldConfiguration->asArray();

        return $this->_responseFields[$responseType];
    }

    /**
     * Initialize and return a field converter class
     *
     * @param string $alias
     * @return Styla_Connect_Model_Api2_Converter_Abstract
     */
    public function getConverter($alias)
    {
        $converterClass = Mage::getModel($alias);
        if (!$converterClass) {
            Mage::throwException("Unknown converter requested: ".$alias);
        }

        return $converterClass;
    }
}