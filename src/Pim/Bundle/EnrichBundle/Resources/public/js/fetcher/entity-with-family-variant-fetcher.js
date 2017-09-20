'use strict';

/**
 * Fetcher for entities with family variant children
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/base-fetcher',
        'routing'

    ],
    function (
        $,
        _,
        BaseFetcher,
        Routing
    ) {
        return BaseFetcher.extend({
            childrenListPromises: {},

            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.childrenListPromises = {};
                this.options = options || {};
            },

            /**
             * Fetch all children of the given parent.
             *
             * @return {Promise}
             */
            fetchChildren: function (parentId) {
                if (!(parentId in this.childrenListPromises)) {
                    if (!_.has(this.options.urls, 'list')) {
                        return $.Deferred().reject().promise();
                    }

                    this.childrenListPromises[parentId] = $.getJSON(
                        Routing.generate(this.options.urls.list), {identifier: parentId}
                    ).then(_.identity).promise();
                }

                return this.childrenListPromises[parentId];
            },

            /**
             * {inheritdoc}
             */
            clear: function () {
                this.childrenListPromises = {};

                BaseFetcher.prototype.clear.apply(this, arguments);
            }
        });
    }
);
