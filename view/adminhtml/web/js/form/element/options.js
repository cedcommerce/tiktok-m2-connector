define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        initialize: function (){
            var user_name = uiRegistry.get('index = user_name');
            var user_password = uiRegistry.get('index = user_password');
            var email = uiRegistry.get('index = email');
            var status = this._super().initialValue;
            if (status == 'admin_token') {
                email.hide();
                user_name.show();
                user_password.show();
            } else{
                email.show();
                user_password.hide();
                user_name.hide();

            }
            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {

            var user_name = uiRegistry.get('index = user_name');
            var user_password = uiRegistry.get('index = user_password');
            var email = uiRegistry.get('index = email');
            if (value == 'admin_token') {
                email.hide();
                user_password.show();
                user_name.show();
            } else {
                email.show();
                user_name.hide();
                user_password.hide();
            }
            return this._super();
        },
    });
});
