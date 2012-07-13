/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var Soup = {
    
    init:function(){
       
       var that = this;
       
       
       /**
        * Mini Help
        */
       
       
       $(".Soup-Help").each(function(){
          $("#Soup-Help").append("<div class='alert alert-info'>"+$(this).html()+"</div>")
       })

       
       /**
        * MODULE ACTION
        */
       $(".module-add-action-toggle").click(function(){
         $("#"+$(this).attr("rel")).toggle();  
       }) 
       
       
       this.DBAlias.init();

       this.Routes.init()
    },
    
   
    
    Models:{
        defaultKeys : ["_id","id","ID"],
        
        init:function(){
            

        }
    },
    
    /**
     * DB ALIAS
     */
    DBAlias:{
        
        Data : {},
        
        init:function(){
            var that = this;
            $("#db-alias-select-db-type").change(function(){
                var sel = $(this).val()
                that.selectTypeForm(sel)
            })
            
            $(".db-alias-load-info").click(function(){           
                var aliasName = $(this).attr("rel");          
                that.edit(aliasName)
            })
            
            $("#db-alias-new").click(function(){
                that.create();
            })            
        },
        
        
        create:function(){
            
            $("#db-alias-edit-aliasname").show()
            
            $("#form-db-alias")[0].reset();
            
            this.selectTypeForm("MySQL")
            
            $("#text-set-aliasname").html("")
            
        },
        
        edit:function(name){
            
            this.selectTypeForm(this.Data[name].Type)
            
            
            $("#db-alias-edit-aliasname").hide()
            
            $("#db-alias-select-db-type").val(this.Data[name].Type)
            
            $("#db-alias-input-aliasname").val(name)
            
            $("#text-set-aliasname").html(name)
            
            
            /**
             * Fill in the input form
             */
            for(i in this.Data[name]){
                $("#db-alias-input-"+i.toLowerCase()).val(this.Data[name][i])
            }
        },
        
        selectTypeForm:function(type){
            
            $("#db-alias-mongodb-options").hide();
            $(".db-alias-no-sqlite-options").hide();     
            $("#db-alias-db-name-options").hide();
            var description = ""
            
            switch(type){
                case "MongoDB":
                    $("#db-alias-mongodb-options").show()
                    $(".db-alias-no-sqlite-options").show();
                    $("#db-alias-db-name-options").show();
                    description = "Edit MongoDB information";
                break;

                case "SQLite":
                    $("#db-alias-mongodb-options").hide();
                    $(".db-alias-no-sqlite-options").hide();  
                    $("#db-alias-db-name-options").show();
                    description = "Edit your SQLite DB information";
                break;

                case "MySQL":
                    $(".db-alias-no-sqlite-options").show(); 
                    $("#db-alias-mongodb-options").hide();  
                    $("#db-alias-db-name-options").show();
                    description = "Edit MySQL settings."
                break;
                
                case "NoDB":
                    $("#db-alias-db-name-options").hide();
                    description = "NoDB is an alias that doesn't have a database interface attached to it.";
                break;
            }  
            
            if(description != "")
                $("#db-alias-type-description").text(description)
        }
        
    },
    
    Routes:{
        template:{},
        
        init:function(){
            var that = this
            
            this.template = _.template($("#routes-entry-tpl").text())
            
            
            $("#btn-routes-add").click(function(){
                that.add()
            })
            
            $("#routes-table-trs").on("click",".routes-remove",function(){
                that.remove($(this).parent().parent())
            })
            
        },
        
        add:function(){

            $("#routes-table-trs").append(this.template)
        },
        
        remove:function(el){
            el.remove()
        }
    }

    
}


$(document).ready(function(){
    Soup.init();
})

