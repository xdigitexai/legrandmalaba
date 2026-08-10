<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* test.twig */
class __TwigTemplate_3d53cfa5fb5008553182f721ea3dd4f436ac979c0531d5809b56c3af0dc4b8d9 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        $this->loadTemplate("header.twig", "test.twig", 1)->display($context);
        // line 2
        echo "
<link href=\"https://cdn.tsm-storage.site/theme/TSM%20[%20SMMSUN%202%20]/css/4g3dwcb2uecqmocp_ekquzu.css\" rel=\"stylesheet\">
<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<link href=\"https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css\" rel=\"stylesheet\">
<style>
.table-view {display: none; }
.responsive-table {overflow-x: auto; }
.responsive-table table {min-width: 600px, width: 100%;}
.mt-6 { margin-top: 1.5rem; }
.flex { display: flex; overflow-x: auto; }
.space-x-1 > :not([hidden]) ~ :not([hidden]) { margin-left: 0.25rem; }
</style>

<div class=\"component_navbar\"></div>
<div class=\"component_navbar_sub\"></div>
<br><br><br>

<div class=\"wrapper-content\">
    <div class=\"wrapper-content__header\"></div>
    <div class=\"wrapper-content__body\">
        <div class=\"min-h-screen bg-gray-100 p-3\">
            <div class=\"mb-1 mt-2\">
                <div class=\"flex items-center text-2xl font-bold text-gray-800\">
                    <i class=\"fas fa-server mr-2 style-text-primary\"></i>
                    <span class=\"style-text-primary\">Order/Service Analytics</span>
                </div>
                <p class=\"mt-1 text-sm font-bold text-gray-600 uppercase\">
                    Explore all recent order completions with real-time average processing times, enabling faster checks and helping you effortlessly discover the best-performing services on ";
        // line 30
        echo (($__internal_compile_0 = ($context["site"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["site_name"] ?? null) : null);
        echo ".
                </p>
            </div>
            
            <div class=\"mb-3 flex justify-end\">
                <form id=\"dateForm\" class=\"mr-2\">
                    <input
                        type=\"date\"
                        name=\"date\"
                        id=\"datePicker\"
                        value=\"";
        // line 40
        echo twig_date_format_filter($this->env, "now", "Y-m-d");
        echo "\"
                        class=\"px-3 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500\"
                        onchange=\"fetchData()\">
                </form>
                <button id=\"toggleView\" class=\"px-3.5 py-2 style-bg-primary text-white rounded-md hover:opacity-90 focus:outline-none\">
                    <i class=\"fas fa-table\"></i> 
                </button>
            </div>
            
            <div class=\"mb-6\">
                <div class=\"mx-auto\">
                    <form id=\"history-search\">
                        <div class=\"flex\">
                            <input class=\"w-full px-3 py-1.5 border border-gray-300 rounded-l-lg focus:outline-none\" type=\"text\" name=\"search\" id=\"searchInput\" placeholder=\"Search For Service or Order IDs\">
                            <button type=\"button\" onclick=\"fetchData()\" class=\"px-4 py-1.5 style-bg-primary text-white rounded-r-lg hover:opacity-90 focus:outline-none\">
                                <i class=\"fa fa-search\"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- CARD VIEW -->
            <div id=\"cardView\" class=\"grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mb-6\" style=\"display: grid;\">
                <!-- Orders will be dynamically populated here -->
            </div>

            <!-- ADDED: TABLE VIEW -->
            <div id=\"tableView\" class=\"responsive-table mb-6\" style=\"display: none;\">
                <table class=\"min-w-full bg-white\">
                    <thead>
                        <tr class=\"bg-gray-100\">
                            <th class=\"py-2 px-4 border-b text-left\">Order ID</th>
                            <th class=\"py-2 px-4 border-b text-left\">Service</th>
                            <th class=\"py-2 px-4 border-b text-left\">Date</th>
                            <th class=\"py-2 px-4 border-b text-left\">Quantity</th>
                            <th class=\"py-2 px-4 border-b text-left\">Duration</th>
                            <th class=\"py-2 px-4 border-b text-left\">Charge</th>
                            <th class=\"py-2 px-4 border-b text-left\">Action</th>
                        </tr>
                    </thead>
                    <tbody id=\"tableBody\">
                        <!-- Table rows will be dynamically populated here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id=\"pagination\" class=\"mt-6 flex overflow-x-auto\">
                <!-- Pagination links will be dynamically populated here -->
            </div>
        </div>
    </div>
</div>

<script>
// Function to fetch data from the API
function fetchData(page = 1) {
    const apiKey = 'teteafae'; // Replace with your actual API key
    const date = document.getElementById('datePicker').value; // Get the selected date
    const search = document.getElementById('searchInput').value; // Get the search keyword

    // Build the API URL with query parameters
    let apiUrl = `https://rental.jinnishikawa.com/api/orders?api_key=\${apiKey}&page=\${page}`;
    if (date) {
        apiUrl += `&date=\${date}`;
    }
    if (search) {
        apiUrl += `&search=\${search}`;
    }

    // Fetch data from the API
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('API response:', data); // Log the API response
            if (data.status === 'success') {
                console.log('Data:', data.data); // Log the data
                if (data.data.length === 0) {
                    console.log('No data found'); // Log a message if no data is found
                } else {
                    // Populate the card view
                    const cardView = document.getElementById('cardView');
                    cardView.innerHTML = data.data.map(order => `
                        <div class=\"bg-white border border-gray-200 rounded-lg shadow-sm\">
                            <div class=\"flex justify-between items-center p-2 pl-4 pr-4 border-b border-gray-200\">
                                <div class=\"w-12 h-7 flex items-center justify-center style-bg-primary rounded-lg\">
                                    <span class=\"text-black font-bold text-xs\">#\${order.id}</span>
                                </div>
                                \${order.date === new Date().toISOString().split('T')[0] ? `
                                <div class=\"px-3 h-7 flex items-center justify-center bg-green-100 rounded-md\">
                                    <span class=\"text-green-500 font-bold text-xs\">Today's order</span>
                                </div>
                                ` : ''}
                                <div style=\"font-size: 13px !important;\" class=\"px-3 py-1 bg-gray-100 rounded-lg text-sm style-text-primary\">
                                    <b><i class=\"fas fa-calendar mr-1\"></i>
                                    <span>\${order.date}</span></b>
                                </div>
                            </div>
                            
                            <div class=\"p-2 pl-4 pr-4\">
                                <div class=\"text-md font-semibold text-gray-800 mb-2\">
                                    <span class=\"text-lg font-bold style-text-primary\">\${order.service_id}</span> - \${order.service}
                                </div>
                                <div class=\"flex justify-between items-center text-xs text-gray-600\">
                                    <div class=\"flex items-center\">
                                        <i class=\"fa fa-star mr-1\"></i>
                                        <span>Quantity: <b>\${order.quantity}</b></span>
                                    </div>
                                    <div class=\"flex items-center\">
                                        <i class=\"fa fa-clock mr-1\"></i>
                                        <span>Completion time: < b>\${order.duration}</b></span>
                                    </div>
                                </div>
                            </div>
                            <div class=\"p-2 pl-4 pr-4 border-t border-gray-200\">
                                <div class=\"flex justify-between items-center\">
                                    <div class=\"text-lg font-bold style-text-primary\">
                                        \${order.charge}
                                    </div> 
                                    <a href=\"\${site.url}/?category_id=\${order.category_id}&service_id=\${order.service_id}\" 
                                        class=\"text-md h-7 font-bold style-bg-primary rounded -lg px-3 flex items-center justify-center hover:opacity-90\">
                                        <span class=\"text-black font-bold\">Create Order</span>
                                    </a> 
                                </div>
                            </div>
                        </div>
                    `).join('');
                    
                    // Populate the table view
                    const tableBody = document.getElementById('tableBody');
                    tableBody.innerHTML = data.data.map(order => `
                        <tr>
                            <td class=\"py-2 px-4 border-b\">#\${order.id}</td>
                            <td class=\"py-2 px-4 border-b\">\${order.service_id} - \${order.service}</td>
                            <td class=\"py-2 px-4 border-b\">\${order.date}</td>
                            <td class=\"py-2 px-4 border-b\">\${order.quantity}</td>
                            <td class=\"py-2 px-4 border-b\">\${order.duration}</td>
                            <td class=\"py-2 px-4 border-b\">\${order.charge}</td>
                            <td class=\"py-2 px-4 border-b\">
                                <a href=\"\${site.url}/?category_id=\${order.category_id}&service_id=\${order.service_id}\" 
                                    class=\"text-md font-bold style-bg-primary rounded-lg px-3 py-1 flex items-center justify-center hover:opacity-90\">
                                    <span class=\"text-black\">Order</span>
                                </a>
                            </td>
                        </tr>
                    `).join('');

                    // Handle pagination
                    const pagination = document.getElementById('pagination');
                    pagination.innerHTML = '';
                    for (let i = 1; i <= data.pagination.total_pages; i++) {
                        pagination.innerHTML += `
                            <li class=\"\${i === data.pagination.current_page ? 'active' : ''}\">
                                <a class=\"btn btn-actions\" href=\"#\" onclick=\"fetchData(\${i})\">\${i}</a>
                            </li>
                        `;
                    }
                }
            } else {
                console.error('Error fetching data:', data.error);
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

// Initial data fetch
document.addEventListener('DOMContentLoaded', fetchData);

// Toggle between card and table view
document.getElementById('toggleView').addEventListener('click', () => {
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    if (cardView.style.display === 'none') {
        cardView.style.display = 'grid';
        tableView.style.display = 'none';
    } else {
        cardView.style.display = 'none';
        tableView.style.display = 'block';
    }
});
</script>";
    }

    public function getTemplateName()
    {
        return "test.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  82 => 40,  69 => 30,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "test.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/test.twig");
    }
}
