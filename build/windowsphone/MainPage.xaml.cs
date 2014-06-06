using System;
using System.ComponentModel;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.IO;
using System.IO.IsolatedStorage;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Navigation;
using Microsoft.Phone.Controls;
using Microsoft.Phone.Shell;

namespace NameYourBabe
{
    public partial class MainPage : PhoneApplicationPage
    {
        // Url of Home page
        private string MainUri = "/Html/index.html";
        private string storeFile = "localStorage_.txt";

        // Constructor
        public MainPage()
        {
            InitializeComponent();
        }

        private void Browser_Loaded(object sender, RoutedEventArgs e)
        {
            // Add your URL here
//            Browser.IsScriptEnabled = true;
            Browser.Navigate(new Uri(MainUri, UriKind.Relative));
//            Browser.IsScriptEnabled = true;
        }

        // Navigates back in the web browser's navigation stack, not the applications.
        private void BackApplicationBar_Click(object sender, EventArgs e)
        {
            Browser.GoBack();
        }

        // Navigates forward in the web browser's navigation stack, not the applications.
        private void ForwardApplicationBar_Click(object sender, EventArgs e)
        {
            Browser.GoForward();
        }

        // Navigates to the initial "home" page.
        private void HomeMenuItem_Click(object sender, EventArgs e)
        {
            Browser.Navigate(new Uri(MainUri, UriKind.Relative));
        }

        // Handle navigation failures.
        private void Browser_NavigationFailed(object sender, System.Windows.Navigation.NavigationFailedEventArgs e)
        {
            MessageBox.Show("Navigation to this page failed, check your internet connection");
        }

        // --------------------------------------------------------------------

        private void browser_ScriptNotify(object sender, NotifyEventArgs e)
        {
            string[] paramVec = e.Value.Split(':');

            if (paramVec[0] == "localStorageGetItem")
            {
                getItem(paramVec[1]);
            }
            else if (paramVec[0] == "localStorageSetItem")
            {
                setItem(paramVec[1]);
            }
            else if (paramVec[0] == "applicationBarClear")
            {
                ApplicationBar.Buttons.Clear();
                ApplicationBar.MenuItems.Clear();
                ApplicationBar.IsVisible = false;
            }
            else if (paramVec[0] == "applicationBarAddButton")
            {
                addButton(paramVec[1], paramVec[2], paramVec[3], paramVec[4]);
            }
            else if (paramVec[0] == "applicationBarAddMenu")
            {
                addMenu(paramVec[1], paramVec[2], paramVec[3]);
            }
        }

        // --------------------------------------------------------------------

        private void getItem(string funcName)
        {
            using (IsolatedStorageFile store = IsolatedStorageFile.GetUserStoreForApplication())
            {
                String ret = "";
                if (store.FileExists(storeFile))
                {
                    using (var fileStream = new IsolatedStorageFileStream(storeFile, FileMode.Open, store))
                    {
                        using (var reader = new StreamReader(fileStream))
                        {
                            ret = reader.ReadLine();
                            reader.Close();
                        }
                    }
                }

                var response = new object[]
                {
                    ret, 2
                };
                Browser.InvokeScript(funcName, response.Select(c => c.ToString()).ToArray());
            }
        }

        // --------------------------------------------------------------------

        private void setItem(string txt)
        {
            using (IsolatedStorageFile store = IsolatedStorageFile.GetUserStoreForApplication())
            {
                using (var stream = store.CreateFile(storeFile))
                {
                    using (var writer = new StreamWriter(stream))
                    {
                        writer.Write( txt);
                    }
                }
            }
        }

        // --------------------------------------------------------------------

        private void addButton(string txt, string iconUrl, string funcName, string funcParams)
        {
            // http://msdn.microsoft.com/en-us/library/windowsphone/develop/ff431806%28v=vs.105%29.aspx
            // http://modernuiicons.com/
            // C:\Program Files (x86)\Microsoft SDKs\Windows Phone\v8.0\Icons\Dark

            var button = new ApplicationBarIconButton();
            button.Click += (sender, e) => Browser.InvokeScript(funcName, funcParams);
            button.IconUri = new Uri("/Html/" + iconUrl, UriKind.Relative);
            button.Text = txt;
            ApplicationBar.Buttons.Add( button);

            if(( ApplicationBar.Buttons.Count + ApplicationBar.MenuItems.Count) == 1) {
                ApplicationBar.IsVisible = true;
            }
        }

        // --------------------------------------------------------------------

        private void addMenu(string txt, string funcName, string funcParams)
        {
            var menu = new ApplicationBarMenuItem();
            menu.Click += (sender, e) => Browser.InvokeScript(funcName, funcParams);
            menu.Text = txt;
            ApplicationBar.MenuItems.Add(menu);

            if ((ApplicationBar.Buttons.Count + ApplicationBar.MenuItems.Count) == 1) {
                ApplicationBar.IsVisible = true;
            }
        }

        // --------------------------------------------------------------------

        protected override void OnBackKeyPress(CancelEventArgs e)
        {
            base.OnBackKeyPress(e);

            if (this.NavigationService.CanGoBack) {
                e.Cancel = true;
                this.NavigationService.GoBack();
            }

            if (Browser.CanGoBack) {
                e.Cancel = true;
                Browser.GoBack();
            }
        }

    }
}