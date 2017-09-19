'use strict';

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
