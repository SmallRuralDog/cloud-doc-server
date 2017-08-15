<style scoped>
    .wrapper-header-nav-logo {
        height: 50px;
        margin-top: 15px;
        float: left;
    }
    .wrapper-header-nav-logo img {
        height: 100%;
    }
    .wrapper-header-nav-list {
        height: inherit;
        float: right;
    }

    .wrapper-header-nav-list:after {
        content: "";
        display: block;
        clear: both;
    }
</style>
<template>
    <div class="wrapper-header">
        <Menu mode="horizontal" :theme="theme1" active-name="1">
            <div class="wrapper-header-nav">.
                <a href="/" class="wrapper-header-nav-logo router-link-active"><img src="https://file.iviewui.com/dist/fe8d29da1225d943e30f9ee1bddce78f.png"></a>
                <div class="wrapper-header-nav-list">

                    <Menu-item name="1">
                        <Icon type="user"></Icon>
                        退出登录
                    </Menu-item>
                </div>
            </div>
        </Menu>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                theme1: 'light'
            }
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>