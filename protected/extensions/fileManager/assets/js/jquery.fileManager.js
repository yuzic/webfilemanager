(function( $ ) {
	$.fn.fileManager = function(settings) {
		var options = $.extend({
			csrfTokenName: 'YII_CSRF_TOKEN',
			csrfToken: '',
			pickerSelector: '#uploadInput',
			createFileRoute: '',
			createDirectoryRoute: '',
            deleteDirectoryRoute: '',
			deleteFileRoute: '',
            listFile: '',
			fileIdInputSelector: '',
            directoryNameSelector:'#directory-name-selector',
            listContainer:'#file-list-container',
            directoryId: 1,
            historyNaviagation:[]
		}, settings);
		
		var galleryInput = $(options.fileIdInputSelector);
		
		return this.each(function() {
			var self = $(this);
            // create directory
			$(document).on('click', '.add-directory-button', function(){
                createDirectory();
		    });

            // event by chanage upload button
            $(document).on('change', options.pickerSelector, function() {
                uploadFile();
            });

            // delete file button
            $(document).on('click', '.delete-file-button', function() {
                var elementSelector = $(this).parents('li');
                deleteFile(elementSelector.attr('entityId'), elementSelector.attr('type'));
            });

            // up to directory
            $(document).on('click', '.directory-up', function() {
                options.historyNaviagation.pop();
                getListFromDirectory(options.historyNaviagation.pop());
                options.historyNaviagation.join();
            });

             // navigation by direcotory
            $(document).on('click', '.title-list-element', function() {
                var elementSelector = $(this).parents('li');
                if (elementSelector.attr('type') === 'directory'){
                    options.directoryId = elementSelector.attr('entityId');
                    options.historyNaviagation.push(options.directoryId);
                    getListFromDirectory(options.directoryId);
                }

            });

            function uploadFile() {
	        	var data = new FormData();
	        	var files = $(options.pickerSelector)[0].files;
	        	for (var i = 0; i< files.length; i++){
		        	data.append('File[uploadFile]', $(options.pickerSelector)[0].files[i]);
		        	data.append('File[directoryId]', options.directoryId);
		         	data.append(options.csrfTokenName, options.csrfToken);
		         	$.ajax(options.createFileRoute, {
		         		type: 'POST',
		         		contentType: false,
		         		dataType: 'json',
		         		data: data,
		         		processData: false,
		         		success: function(data) {
                            addFileToContainer(data.model);
		         		},
		         		error: function() {
		         			console.log('error adding photo');
		         		}
		         	});
	        	}
			};

            function createDirectory(){
               var directoryName  = $(options.directoryNameSelector).val();
               if (directoryName === ''){
                   alert("Введите название директории!");
               }else{
                   var data = {};
                   data[options.csrfTokenName] = options.csrfToken;
                   data['name'] = directoryName;
                   data['parentId'] = options.directoryId;
                   $.ajax(options.createDirectoryRoute, {
                       type: "POST",
                       dataType: 'json',
                       data: data,
                       success: function(data) {
                            getListFromDirectory(options.directoryId);
                       },
                       error: function() {
                           console.log('failed create firecory');
                       }
                   });
               }
            };

            function getListFromDirectory(directoryId){
                $(options.listContainer).empty();
                var data = {};
                data[options.csrfTokenName] = options.csrfToken;
                data['directoryId'] = directoryId ;
                $.ajax(options.listFile, {
                    type: "POST",
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        for (var i=0; i < data.model.length; i++) {
                            addFileToContainer(data.model[i]);
                        }
                    },
                    error: function() {
                        console.log('failed to get list from directory');
                    }
                });
            }
			
			function addFileToContainer(model) {
                if (model.type == 'directory'){
                    model.name = '/' + model.name;
                }
                var urlPathSave = '';
                if (model.type == 'file'){
                    urlPathSave = '<a class="save-file-button" href="/fileManager/'+model.path+'/'+model.name+'"\ ' +
                        'title="Скачать" target="_blank"></a>';
                }
				var htmlFragment = '\
                    <li id="preload_' + model.id + '" type="'+ model.type + '" entityId="'+model.id+'">\
                          <div class="title-list-element">' + model.name + '</div>\
                         \ <div class="delete-file-button"  title="Удалить"></div>'+urlPathSave+'</li>';
                $(options.listContainer).append(htmlFragment);
			};
			

			function deleteFile(id, type) {
                var routeUrl = options.deleteFileRoute;
                if (type === 'directory'){
                    routeUrl = options.deleteDirectoryRoute;
                }
				var data = {};
				data[options.csrfTokenName] = options.csrfToken;
				data['id'] = id;
				$.ajax(routeUrl, {
	    	  		 type: "POST",
	    	  		 dataType: 'json', 
	    	  		 contentType: false,
	    	  		 data: data,
	    	  		 success: function(response) {
	    	  			 if (response) {
	    	  				 $('#preload_' + id).remove();
	    	  			 }  
	    	  		 },
                    error: function() {
                        console.log('failed to delete file');
                    }
	    	  	 });
		    };	
		    
		    function listFile() {
		    	$.ajax(options.listFile, {
	    	  		 type: "GET",
	    	  		 dataType: 'json', 
	    	  		 success: function(data) {
                         //clear list container
	    	  			for (var i=0; i < data.model.length; i++) {
                            addFileToContainer(data.model[i]);
	    		    	}
	    	  		 },
	    	  		 error: function() {
	    	  			 console.log('failed to load list file');
	    	  		 }
	    	  	 });
		    };
            listFile();

		});
	};
}( jQuery ));
