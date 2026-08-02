using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Data;
using System.Data.SqlClient;   ///  Required to interact with sql server


namespace MovieTicketManagementSystem
{
    public partial class RegForm : Form    //a windows form name
    {
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True; ";     //connecting string to sql server database
        public RegForm()    // form load and when the application starts constractor
        {
            InitializeComponent();   // sets up the ui(button,textbox,etc...
                        
        }


        private void reg_showPass_CheckedChanged(object sender, EventArgs e)
        {
            reg_password.PasswordChar = reg_showPass.Checked ? '\0' : '*';       //Toggle password visibility based on checkbox state
            reg_cPassword.PasswordChar = reg_showPass.Checked ? '\0' : '*';      //hidden(*) & visible(\0)
        }

        private void close_Click(object sender, EventArgs e)
        {
            Application.Exit();  //close the entire application
        }

        private void reg_btn_Click(object sender, EventArgs e)
        {
            if (reg_username.Text == "" || reg_password.Text == "" || reg_cPassword.Text == "")  //show error message if any fields empty
            {
                MessageBox.Show("please fill all blank fields","Error Message",MessageBoxButtons.OK,MessageBoxIcon.Error);
            }
            else if(reg_password.Text!=reg_cPassword.Text)    // show error if  passwords dont match
            {
                MessageBox.Show("password does not match", "Error Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            else if(reg_password.Text.Length<8)  // show error if password is too short
            {
                MessageBox.Show("Invalid password", "Error Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            else
            {

                using(SqlConnection connect = new SqlConnection(conn))   // connect to sqlServer database     
                {
                    connect.Open();              // open database                          // using keyword automatically closes & dispose database connection & files

                    string checkUsername = "SELECT * FROM users WHERE username = @usern";
                    
                    using(SqlCommand checkUsern=new SqlCommand(checkUsername, connect))  //runs sql commands(SqlCommands)
                    {
                        checkUsern.Parameters.AddWithValue("@usern", reg_username.Text.Trim());    // parameters is parsing the input values

                        SqlDataAdapter adapter = new SqlDataAdapter(checkUsern);    //execute the query 
                        DataTable table = new DataTable();   //tempory table in memory for query result/ save result in datatable

                        adapter.Fill(table);   //fill is copying the results to table 

                        if (table.Rows.Count > 0)
                        {
                            MessageBox.Show(reg_username.Text.Substring(0, 1).ToUpper() + reg_username.Text.Substring(1) + "is taken alreay", "Error Message", MessageBoxButtons.OK,MessageBoxIcon.Error);
                        }
                        else
                        {
                            string insertData = "INSERT INTO users (username,password,role,status,date_reg)" + "VALUES(@usern,@pass,@role,@status,@date)";


                            DateTime today = DateTime.Today;  // represent an instant current time

                            using(SqlCommand cmd =new SqlCommand(insertData, connect))
                            {
                                cmd.Parameters.AddWithValue("@usern", reg_username.Text.Trim());  // parameters avoid aql injections and safely insert values
                                cmd.Parameters.AddWithValue("@pass", reg_password.Text.Trim());    // addWithValue is values in textbox to query
                                cmd.Parameters.AddWithValue("@role","staff");
                                cmd.Parameters.AddWithValue("@status", "Active");
                                cmd.Parameters.AddWithValue("@date",today);
                                
                                int rowsAffected= cmd.ExecuteNonQuery();   // ExecuteNonQuery will be run sql command(insert/update/delete)
                                if (rowsAffected > 0)                      //it does not end the rows,but how many rows it changed will be return
                                {
                                    MessageBox.Show("Registered successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);
                                }
                                else
                                {
                                    MessageBox.Show("Registration filed");
                                }

                                Form1 loginForm = new Form1();
                                loginForm.Show();
                                this.Hide();
                            }
                        }


                    }
                }
            }
        }

        private void reg_signInBtn_Click(object sender, EventArgs e)
        {
            Form1 loginForm = new Form1();
            loginForm.Show();

            this.Hide();

        }
    }
}
