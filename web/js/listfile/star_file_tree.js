/**
 * Created by lawrance on 9/8/16.
 */
'use strict';
(function($) {

    $.fn.starFileTree = function(options) {

        var defaults = {
            data: undefined,
            sortable: false,
            selectable: false
        };
        function initTreeBuilder(elements){
            options = $.extend(defaults, options);

            return elements.each(function() {
                var o = options;
                var obj = $(this);
                var title='<p>This is Header</p>';
                var list = '<ol class="tree">';

                o.data.forEach(function(rootItem){
                    if(rootItem.type === 'dir'){
                        list = list + _renderDir(rootItem);
                       // i++;
                    }else{
                       // list = list + _renderFile(rootItem);
                        //i++;
                    }
                });

                list = list + '</ol>';
                obj.append(list);
                obj.addClass('file-tree');

                // Initial state
                obj.find('li.folder').addClass('mjs-nestedSortable-collapsed');

                // Add listeners
                _bindListeners(obj);

                if(options.sortable){
                    // Sortable
                    _initSortable(obj);
                }else if(options.selectable){
                    // Selectable
                    _initSelectable(obj);
                }
            });
        }
        function _renderDir(dir){
            var listItem;

            if(dir.id !== undefined){
                listItem = '<li class="folder" data-id="' + dir.id + '" data-type="folder" data-name="' + dir.name + '" ><div><span></span>' + dir.name + '</div>';
            }else{
                listItem = '<li class="folder"><div><span></span>' + dir.name + '</div>';
            }

            if(dir.children !== undefined){
                //listItem = listItem + '<ol>' + _loopChildren(dir) + '</ol>';
            }

            listItem = listItem + '</li>';
            listItem=listItem+'<span id="username">'+dir.username+'</span><span id="datetime">'+dir.datetime+'</span><span id="userid">'+dir.password+'</span> '

            return listItem;
        }

        switch (options){
            case 'toObject':
                return toObject(this);
            case 'toJson':
                return toJson(this);
            default:
                return initTreeBuilder(this);
        }
    };
}(jQuery))