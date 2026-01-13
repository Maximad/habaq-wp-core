(function (wp) {
    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;

    registerBlockType('habaq/job-dates', {
        edit: function (props) {
            var postId = props && props.context ? props.context.postId : 0;
            var label = postId ? 'عرض تواريخ الفرصة' : 'ضع البلوك داخل حلقة الاستعلام';
            return el('div', { className: 'habaq-job-dates__placeholder' }, label);
        },
        save: function () {
            return null;
        }
    });
})(window.wp);
