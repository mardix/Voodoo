
$partials/layouts

It contains the layouts. 

Layouts are .html files. Layout

- default.html          : The default layout. 

- header.html           : The header of the site

- footer.html           : The footer of the site

- single-column.html    : An optional layout, if you want to use a single column one

- head-tag.html         : Contains the header tag

You can add other layouts. 

Make sure you have {{!include @action_view}} in the layout so it can display it

Example of a simple layout using bootstrap 3

<HTML>
    {{!include $partials/layouts/head-tag}}

    <body>

        {{!include $partials/layouts/header}}

        <div class="container">
            
            {{!include $partials/components/flash-message}}

            <div class="row">
                <div class="col-md-12">
                    <!-- REQUIRED to show the view's action page -->   
                    {{!include @action_view}}
                </div>
            </div>
        </div>

        {{!include $partials/layouts/footer}}
        
    </body>
</HTML>