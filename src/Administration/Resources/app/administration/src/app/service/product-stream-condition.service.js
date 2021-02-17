const utils = Shopware.Utils;

/**
 * @module app/service/product-stream-condition
 */

/**
 * @memberOf module:app/service/product-stream-condition
 * @constructor
 * @method conditionService
 * @returns {Object}
 */
export default function conditionService() {
    const blacklist = [
        'createdAt',
        'updatedAt',
        'afterCategoryId',
        'versionId',
        'afterCategoryVersionId',
        'autoIncrement',
        'canonicalUrl',
        'children',
        'childCount',
        'facetIds',
        'mediaId',
        'parent',
        'parentId',
        'parentVersionId',
        'sortingIds',
        'metaTitle',
        'metaDescription',
        'metaKeywords',
        'additionalText',
        'products',
        'product',
        'productId',
        'productVersionId',
        'optionId',
        'groupId',
        'media',
        'salesChannelId',
        'typeId',
        'languageId',
        'currencyId',
        'paymentMethodId',
        'shippingMethodId',
        'countryId',
        'navigationId',
        'navigationVersionId',
        'mailHeaderFooterId',
        'manufacturerId',
        'manufacturerNumber',
        'unitId',
        'taxId',
        'coverId',
        'productMediaVersionId',
        'propertyIds',
        'optionIds',
        'orders',
        'customers',
        'seoUrls',
        'translated',
        'tagIds',
        'customerGroupId',
        'newsletterRecipients',
        'numberRanges',
        'promotionSalesChannels',
        'seoUrlTemplates',
        'shippingMethods',
        'markAsTopseller',
        'variantRestrictions',
        'configuratorGroupConfig',
        'cmsPageId',
        'navigationCategoryId',
        'navigationCategoryVersionId',
        'footerCategoryId',
        'footerCategoryVersionId',
        'serviceCategoryId',
        'serviceCategoryVersionId',
        'position',
        'navigationCategory',
        'footerCategory',
        'serviceCategory',
        'numberRangeSalesChannels',
        'documentBaseConfigSalesChannels',
        'translations',
        'translation',
        'mainCategories'
    ];

    const entityBlacklist = {
        price: [
            'linked'
        ],
        tax: [
            'customFields',
            'name',
            'products',
            'productServices'
        ],
        tag: [
            'categories'
        ],
        category: [
            'displayNestedProducts',
            'path',
            'level',
            'template',
            'customFields',
            'cmsDescription',
            'cmsHeadline',
            'createdAt',
            'extensions',
            'external',
            'hideFilter',
            'hideSortings',
            'hideTop',
            'media',
            'navigations',
            'nestedProducts',
            'productBoxLayout',
            'navigationSalesChannels',
            'footerSalesChannels',
            'serviceSalesChannels',
            'cmsPage',
            'externalLink',
            'slotConfig'
        ],
        product_manufacturer: [
            'link',
            'customFields',
            'media',
            'description'
        ],
        unit: [
            'customFields',
            'shortCode'
        ],
        product_configurator_setting: [
            'versionId',
            'prices',
            'createdAt',
            'updatedAt',
            'customFields',
            'id'
        ],
        property_group_option: [
            'colorHexCode',
            'productConfigurators',
            'productServices',
            'productProperties',
            'productOptions',
            'customFields',
            'productConfiguratorSettings'
        ],
        property_group: [
            'description',
            'filterable',
            'comparable',
            'displayType',
            'sortingType',
            'options',
            'customFields'
        ],
        product_visibility: [
            'id'
        ],
        sales_channel: [
            'name',
            'accessKey',
            'configuration',
            'customFields',
            'extensions',
            'type',
            'currencies',
            'languages',
            'countries',
            'paymentMethods',
            'shippingMethods',
            'country',
            'domains',
            'systemConfigs',
            'navigation',
            'productVisibilities',
            'mailHeaderFooter',
            'mailTemplates',
            'language',
            'paymentMethod',
            'shippingMethod',
            'currency',
            'customerGroup',
            'shortName',
            'themes'
        ],
        product: [
            'blacklistIds',
            'whitelistIds',
            'productManufacturerVersionId',
            // @feature-deprecated (flag:FEATURE_NEXT_10553) tag:v6.4.0 - will be removed
            'listingPrices',
            'categoryTree',
            'extensions',
            'productServices',
            'cover',
            'metaTitle',
            'prices',
            'services',
            'searchKeywords',
            'categories',
            'canonicalUrl',
            'purchaseSteps',
            'options',
            'customFields'
        ]
    };

    const allowedProperties = [
        'id'
    ];

    const entityAllowedProperties = {
        tag: [
            'id'
        ],
        category: [
            'id'
        ],
        product_manufacturer: [
            'id'
        ],
        property_group_option: [
            'id'
        ],
        property_group: [
            'id'
        ],
        product_visibility: [
            'id'
        ],
        sales_channel: [
            'id'
        ],
        product: [
            'id',
            'active',
            'name',
            'description',
            'ratingAverage',
            'price',
            'productNumber',
            'stock',
            'availableStock',
            'releaseDate',
            'tags',
            'weight',
            'height',
            'length',
            'sales',
            'manufacturer',
            'categoriesRo',
            'shippingFree',
            'visibilities',
            'properties'
        ]
    };

    const productFilterTypes = {
        equals: {
            identifier: 'equals',
            label: 'sw-product-stream.filter.type.equals'
        },

        equalsAny: {
            identifier: 'equalsAny',
            label: 'sw-product-stream.filter.type.equalsAny'
        },

        contains: {
            identifier: 'contains',
            label: 'sw-product-stream.filter.type.contains'
        },

        lessThan: {
            identifier: 'lessThan',
            label: 'sw-product-stream.filter.type.lessThan'
        },

        greaterThan: {
            identifier: 'greaterThan',
            label: 'sw-product-stream.filter.type.greaterThan'
        },

        lessThanEquals: {
            identifier: 'lessThanEquals',
            label: 'sw-product-stream.filter.type.lessThanEquals'
        },

        greaterThanEquals: {
            identifier: 'greaterThanEquals',
            label: 'sw-product-stream.filter.type.greaterThanEquals'
        },

        notEquals: {
            identifier: 'notEquals',
            label: 'sw-product-stream.filter.type.notEquals'
        },

        notEqualsAny: {
            identifier: 'notEqualsAny',
            label: 'sw-product-stream.filter.type.notEqualsAny'
        },

        notContains: {
            identifier: 'notContains',
            label: 'sw-product-stream.filter.type.notContains'
        },

        range: {
            identifier: 'range',
            label: 'sw-product-stream.filter.type.range'
        },

        not: {
            identifier: 'not',
            label: 'sw-product-stream.filter.type.not'
        }
    };

    const operatorSets = {
        boolean: [
            productFilterTypes.equals
        ],
        string: [
            productFilterTypes.equals,
            productFilterTypes.notEquals,
            productFilterTypes.equalsAny,
            productFilterTypes.notEqualsAny,
            productFilterTypes.contains,
            productFilterTypes.notContains
        ],

        date: [
            productFilterTypes.equals,
            productFilterTypes.greaterThan,
            productFilterTypes.greaterThanEquals,
            productFilterTypes.lessThan,
            productFilterTypes.lessThanEquals,
            productFilterTypes.notEquals,
            productFilterTypes.range
        ],

        uuid: [
            productFilterTypes.equals,
            productFilterTypes.notEquals,
            productFilterTypes.equalsAny,
            productFilterTypes.notEqualsAny
        ],

        int: [
            productFilterTypes.equals,
            productFilterTypes.greaterThan,
            productFilterTypes.greaterThanEquals,
            productFilterTypes.lessThan,
            productFilterTypes.lessThanEquals,
            productFilterTypes.notEquals,
            productFilterTypes.range
        ],

        float: [
            productFilterTypes.equals,
            productFilterTypes.greaterThan,
            productFilterTypes.greaterThanEquals,
            productFilterTypes.lessThan,
            productFilterTypes.lessThanEquals,
            productFilterTypes.notEquals,
            productFilterTypes.range
        ],

        object: [
            productFilterTypes.equals,
            productFilterTypes.greaterThan,
            productFilterTypes.greaterThanEquals,
            productFilterTypes.lessThan,
            productFilterTypes.lessThanEquals,
            productFilterTypes.notEquals,
            productFilterTypes.range
        ],

        default: [
            productFilterTypes.equals,
            productFilterTypes.notEquals,
            productFilterTypes.equalsAny,
            productFilterTypes.notEqualsAny
        ]
    };

    return {
        isPropertyInBlacklist,
        addToGeneralBlacklist,
        addToEntityBlacklist,
        removeFromGeneralBlacklist,
        removeFromEntityBlacklist,
        isPropertyInAllowList,
        addToGeneralAllowList,
        addToEntityAllowList,
        removeFromGeneralAllowList,
        removeFromEntityAllowList,
        getConditions,
        getAndContainerData,
        isAndContainer,
        getOrContainerData,
        isOrContainer,
        getPlaceholderData,
        getComponentByCondition,
        getOperatorSet,
        negateOperator,
        getOperator,
        isNegatedType,
        isRangeType
    };

    /**
     * @feature-deprecated (flag:FEATURE_NEXT_12158) - We will use a allowlist pattern instead of a blacklist
     * @param {?string} definition
     * @param {string} property
     * @returns {boolean}
     */
    function isPropertyInBlacklist(definition, property) {
        return blacklist.includes(property)
            || (entityBlacklist.hasOwnProperty(definition) && entityBlacklist[definition].includes(property));
    }

    /**
     * @feature-deprecated (flag:FEATURE_NEXT_12158) - We will use a allowlist pattern instead of a blacklist
     * @param {string[]} properties
     */
    function addToGeneralBlacklist(properties) {
        blacklist.push(...properties);
    }

    /**
     * @feature-deprecated (flag:FEATURE_NEXT_12158) - We will use a allowlist pattern instead of a blacklist
     * @param {string} entity
     * @param {string[]} properties
     */
    function addToEntityBlacklist(entity, properties) {
        if (entityBlacklist[entity]) {
            entityBlacklist[entity].push(...properties);
            return;
        }

        entityBlacklist[entity] = properties;
    }

    /**
     * @feature-deprecated (flag:FEATURE_NEXT_12158) - We will use a allowlist pattern instead of a blacklist
     * @param {string[]} properties
     */
    function removeFromGeneralBlacklist(properties) {
        properties.forEach(entry => {
            blacklist.splice(blacklist.indexOf(entry), 1);
        });
    }

    /**
     * @feature-deprecated (flag:FEATURE_NEXT_12158) - We will use a allowlist pattern instead of a blacklist
     * @param {string} entity
     * @param {string[]} properties
     */
    function removeFromEntityBlacklist(entity, properties) {
        if (!entityBlacklist[entity]) {
            return;
        }

        properties.forEach(entry => {
            entityBlacklist[entity].splice(entityBlacklist[entity].indexOf(entry), 1);
        });
    }

    /**
     * @param {?string} definition
     * @param {string} property
     * @returns {boolean}
     */
    function isPropertyInAllowList(definition, property) {
        return allowedProperties.includes(property)
            || (entityAllowedProperties.hasOwnProperty(definition) && entityAllowedProperties[definition].includes(property));
    }

    /**
     * @param {string[]} properties
     */
    function addToGeneralAllowList(properties) {
        allowedProperties.push(...properties);
    }

    /**
     * @param {string} entity
     * @param {string[]} properties
     */
    function addToEntityAllowList(entity, properties) {
        if (entityAllowedProperties[entity]) {
            entityAllowedProperties[entity].push(...properties);
            return;
        }

        entityAllowedProperties[entity] = properties;
    }

    /**
     * @param {string[]} properties
     */
    function removeFromGeneralAllowList(properties) {
        properties.forEach(entry => {
            allowedProperties.splice(allowedProperties.indexOf(entry), 1);
        });
    }

    /**
     * @param {string} entity
     * @param {string[]} properties
     */
    function removeFromEntityAllowList(entity, properties) {
        if (!entityAllowedProperties[entity]) {
            return;
        }

        properties.forEach(entry => {
            entityAllowedProperties[entity].splice(entityAllowedProperties[entity].indexOf(entry), 1);
        });
    }

    function getConditions() {
        return [
            {
                type: 'productStreamFilter',
                component: 'sw-product-stream-filter',
                label: 'product',
                scopes: ['product']
            }
        ];
    }

    function getAndContainerData() {
        return { type: 'multi', field: null, parameters: null, operator: 'AND' };
    }

    function isAndContainer(condition) {
        return condition.type === 'multi' && condition.operator === 'AND';
    }

    function getOrContainerData() {
        return { type: 'multi', field: null, parameters: null, operator: 'OR' };
    }

    function isOrContainer(condition) {
        return condition.type === 'multi' && condition.operator === 'OR';
    }

    function getPlaceholderData() {
        return { type: 'equals', field: 'id', parameters: null, operator: null };
    }

    function getComponentByCondition(condition) {
        if (isAndContainer(condition)) {
            return 'sw-condition-and-container';
        }

        if (isOrContainer(condition)) {
            return 'sw-condition-or-container';
        }

        return 'sw-product-stream-filter';
    }

    function getOperatorSet(type) {
        if (!utils.types.isString(type) || type === '') {
            return operatorSets.default;
        }

        return operatorSets[type] || operatorSets.default;
    }

    function getOperator(type) {
        return productFilterTypes[type];
    }

    function negateOperator(type) {
        switch (type) {
            case 'equals':
                return productFilterTypes.notEquals;
            case 'notEquals':
                return productFilterTypes.equals;
            case 'equalsAny':
                return productFilterTypes.notEqualsAny;
            case 'notEqualsAny':
                return productFilterTypes.equalsAny;
            case 'contains':
                return productFilterTypes.notContains;
            case 'notContains':
                return productFilterTypes.contains;
            default:
                return productFilterTypes[type] || null;
        }
    }

    function isNegatedType(type) {
        return [
            productFilterTypes.notContains.identifier,
            productFilterTypes.notEqualsAny.identifier,
            productFilterTypes.notEquals.identifier
        ].includes(type);
    }

    function isRangeType(type) {
        return [
            productFilterTypes.lessThan.identifier,
            productFilterTypes.lessThanEquals.identifier,
            productFilterTypes.greaterThan.identifier,
            productFilterTypes.greaterThanEquals.identifier,
            productFilterTypes.range.identifier
        ].includes(type);
    }
}
